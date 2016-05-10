<?php
function selectParam($field,$name=false)
{
    global $sps;
    if(!$name) {
        $name = $field;
    }
        // if we're using the router don't bother with executing javascript
        if (array_key_exists('router', $sps->settings)) {
            $require = true;
            $onchange = '';
        } else {
            $require = false;
        }
    if (isset($sps->filters) && isset($sps->filters[$field])) {
        $current_value = $sps->filters[$field];
    } else {
        $current_value = '';
    }
    $filterKeys = retArrayParams();
    if (in_array($field, $filterKeys)) {
        if (!$require) {
            $onchange = 'onchange="sps.setFilters({\''.$field.'\': this.value})"';
        }
        $html = "<label>$name</label>";
        $html .= "<select class='btn' id='$field"."_select' name='$field' $onchange>";
        $html .= "<option value=''></option>";
        $filterValues = retArrayParamValues($field);
        foreach ($filterValues as $value) {
            if ($value == $current_value) {
                $selected = 'selected';
            } else {
                $selected = '';
            }
            $html .= "<option $selected value='$value'>$value</option>";
        }
        $html .= '</select>';
    } else {
        //$html = "<div>Error: $field not found!</div>";
        $html = "";
    }

    return $html;
}
function topMenu()
{
    global $sps,$config;
    $aliases = array();
    $aliases["pendingshipments"] = "view samples";
    $aliases["crf"] = "import samples";
    $study = $sps->active_study;
    $username = $sps->username;
    $taskArray = array();
    $roles = $sps->auth->roles;
    $current_task = $sps->task;
    $task_behavior = $sps->task_behavior;
        // get special tasks
        $task_behavior = $sps->task_behavior;
    $InventoryMenu = array("Site View", "Sample Edit","Consolidate");
    //$CrfMenu = array("Store","Pending Shipments","Receipt", "Pull");
    $CrfMenu = array("Pending Shipments");
    $ResultsMenu = array("QC","Import Results","LIS","Orders","Analysis");
    $AdminMenu = array("Roles");
    if (isset($task_behavior['pulladmin'])) {
        array_push($AdminMenu, 'Pull Admin');
    }
    $html = '    <div id="topmenu" class="navbar navbar-fixed-top">'."\n";
    $html .= '        <div class="control-group buttonbar dropdown">'."\n";
    $html .= '            <ul class="nav">'."\n";
    if (isset($sps->active_study->id_study)) {
        if (in_array('lab', $roles)) {
            //$taskArray['Inventory'] =  $InventoryMenu;
            $taskArray['crf'] =  $CrfMenu;
            //$taskArray['Results'] =  $ResultsMenu;
        }
        if (in_array('admin', $roles)) {
            $taskArray['Admin'] =  $AdminMenu;
        }

        foreach ($taskArray as $key => $task) {
            $supertask = strtolower($key);

            if(isset($aliases[$supertask])) {
                $slinkname = $aliases[$supertask];
            } else {
                $slinkname = $key;
            }

            if ($current_task == $supertask) {
                $active = $supertask;
                $active_class = 'active';
            } else {
                $active_class = '';
            }
            if (count($task) > 0) {
                $open  = '';
                $subbutton = '';
                $carrot_class = '';
                foreach ($task as $subtask) {
                    $short = str_replace(' ', '', $subtask);
                    $short = strtolower($short);
                    # for aliases
                    if(isset($aliases[$short])) {
                        $linkname = $aliases[$short];
                    } else {
                        $linkname = $subtask;
                    }
                    $subbutton .= "<li><a href='index.php?task=$short#task/$short'>";
                    if ($current_task == $short) {
                        $active = $supertask;
                        $subbutton .= '-';
                        $carrot_class = 'active';
                        $open = 'open';
                    }
                    $subbutton .= $linkname.'</a></li>'."\n";
                }

                $supertask_button = $supertask.'_button';
                $superbutton = "<a href='index.php?task=$supertask#task/$supertask' id='$supertask_button' class='btn $active_class btn-sm btn-sps'>$slinkname</a>\n";
                $superbutton .= '<button class="btn btn-sm btn-sps '.$carrot_class.' btn-info " data-toggle="dropdown">'."\n";
                $superbutton .= '  <span class="caret '.$carrot_class.'"></span>'."\n";
                $superbutton .= '</button>'."\n";
                $superbutton .= '  <ul class="dropdown-menu">'."\n";
                $superbutton .= $subbutton;

                $superbutton .= '  </ul>'."\n";
            } else {
                $superbutton .= '  </ul>'."\n";
                $superbutton = "<a href='index.php?task=$supertask#task/$supertask' id='$supertask_button' class='btn $active_class $carrot_class btn-sm btn-sps'>$slinkname</a>\n";
            }
            $html .= '<div class="btn-group btn-sps">'."\n";
            $html .= $superbutton;
            $html .= '</div>'."\n";
        }

        $html .= '         <span id="scanner">';
        $html .= '                     <input type="text"  vertical-align:middle id="scanIn" name="scanIn" autocorrect="off" autocomplete="off" class="input" value="">';
        //$html .= '                     <button id="reset_button" class="btn btn-sm btn-warning">Reset</button>'."\n";
        //$html .= '         </span>'."\n";
    }

    $html .= '<ul class="nav pull-right">';
    $html .=  "<span id='cue'></span>";
    $html .=  "<i>$sps->environment</i> <span app-version></span>";
    $html .= '                     <a class="btn btn-sm" href="https://weblogin.pennkey.upenn.edu/logout">Logout ('.$sps->username.')</a>'."\n";
    $html .= '</ul>';
    $html .= '         </ul>'."\n";
    $html .= '         </div>'."\n";
    $html .= '             </div>'."\n";
    $html .= '         </div>'."\n";
    $html .= '     </div>'."\n";

    return $html;
}
function describeAnidatedArray($taskArray, $task)
{
    $html = '';
    foreach ($taskArray as $key => $value) {
        if (is_array($value)) {
            $shorttaskname = str_replace(' ', '', strtolower($key));
            $subitemSelected = false;
            foreach ($value as $subitem) {
                //there's some kind of magic here.. i don't know how this works -srv
                                $subitemSelected = $subitemSelected || $task == str_replace(' ', '', strtolower($subitem));
            }
            if ($task == $shorttaskname || $subitemSelected) {
                $selected = 'class="selected"';
            } else {
                $selected = '';
            }
//                        if(iOSDetect() == 'ios') {
//                        $html.= '<li '.$selected.'><a href="'.$link.'">' . $key . '</a>';
//                        } else {
                        $link = "?task=$shorttaskname";
            $html .= '<li '.$selected.'><a href="'.$link.'">'.$key.'</a>';
            $html .= '<ul>'.describeAnidatedArray($value, $task).'</ul>';
        } else {
            $shorttaskname = str_replace(' ', '', strtolower($value));
            if ($shorttaskname == $task) {
                $selected = 'class="selected"';
            } else {
                $selected = '';
            }
            $link = "?task=$shorttaskname";
            $html .= '<li '.$selected.'><a href="'.$link.'">'.$value.'</a></li>';
        }
    }

    return $html;
}

function selectStudy()
{
    global $sps;
    $username = $sps->username;
    $studies = $sps->auth->studies;
    if ($GLOBALS['sps']->active_study) {
        $current_study = $GLOBALS['sps']->active_study->id_study;
    } else {
        $current_study = null;
    }
    $html =  "<div>Study:</div>";
    $html = "<label for='study'>Study</label>";
    if (count($studies) == 0) {
        return "Error:  You are not permitted to access any studies";
        exit;
    } elseif (count($studies) > 1) {
        $html .= '<select class="btn" name="study" id="ccid_study" onChange="filter(\'id_study\',$F(\'ccid_study\'));window.location.reload()">';
        if (!isset($current_study)) {
            $html .=  '<option value=""></option>';
        }
        foreach ($studies as $study) {
            if ($study == $current_study) {
                $sel = 'selected';
            } else {
                $sel = '';
            }
            $html .=  '<option value="'.$study.'" '.$sel.'>'.$study.'</option>'."\n";
        }
        $html .= '</select>';
    } elseif ((count($studies) == 1) && ($current_study != null)) {
        $html = $current_study;
    }

    return $html;
}
function selectPrinter()
{
    global $sps;
    $PrintDev = new PrintDev();
    if ($sps->printer) {
        $PrintDev = $sps->printer;
    }
    $printers = array_keys($PrintDev->listPrinters());
    $current_printer = $PrintDev->printer_name;
    $html =  "<div>Printer:</div>";
    $html .= '<select class="btn" id="ccprinter" onChange="printer = new Printjob;printer.setPrinter($F(\'ccprinter\'))">';
    if (count($printers) == 0) {
        return "Error:  You are not permitted to access any printers";
        exit;
    } elseif (count($printers) > 0) {
        $html .=  '<option value=""></option>'."\n";
        foreach ($printers as $printer) {
            if ($printer == $current_printer) {
                $sel = 'selected';
            } else {
                $sel = '';
            }
            $html .=  '<option value="'.$printer.'" '.$sel.'>'.$printer.'</option>'."\n";
        }
        $html .= '</select>';
    } else {
        $html .= "<div>$current_printer</div>";
    }

    return $html;
}
function selectDateRange()
{
    $todays_date = date("Y-m-d");
    $dateend = '';
    $datestart = '';
    if (isset($_SESSION['datestart'])) {
        $datestart = $_SESSION['datestart'];
    }
    if (isset($_SESSION['dateend'])) {
        $dateend = $_SESSION['dateend'];
    }
    $html = "<span id='daterange'><label for='datestart'>from</label>";
    $html .= "<input placeholder='YYYY-MM-DD' name='datestart' class='form-control' id='datestart' type='text' value='$datestart' \>";
    $html .= "<label for='dateend'>to</label>";
    $html .= "<input placeholder='YYYY-MM-DD' name='dateend' class='form-control' id='dateend' type='text' value='$dateend' \>";
    $html .= "<button class='btn' id='go'>go</button>";
    $html .= "<br></span>";

    return $html;
}
