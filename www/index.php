<?php
lib('menu');
if (array_key_exists('router',$sps->settings)) {
    $require = true;
} else {
    $require = false;
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <title>SPS</title>

    <meta charset="utf-8">
    <meta name="description" content="">
<link href="/sps/css/slick.grid.css" rel="stylesheet" type="text/css">
    <link href="/sps/css/backgrid.css" rel="stylesheet" type="text/css">
    <link href="/sps/css/tableorderer.css" rel="stylesheet" type="text/css">
    <link href="/sps/css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="/sps/css/dataTables.bootstrap.css" rel="stylesheet" type="text/css">
    <link href="/sps/css/docs.css" rel="stylesheet" type="text/css">
    <link href="/sps/css/opentip.css" rel="stylesheet" type="text/css">
    <link href="/sps/css/glyph.css" rel="stylesheet" type="text/css">
    <link href="/sps/css/style.css" rel="stylesheet" type="text/css">
    <?php if ($require) {
    ?>
     <script data-main="/sps/js/main?v=3"  src="/sps/js/libs/require/require.js" type="text/javascript"></script>
    <?php } else {
    ?>
    <script src="/sps/js/libs/jquery/jquery-min.js" type="text/javascript"></script>
    <script src="/sps/js/libs/jquery/jquery-serialize.js" type="text/javascript"></script>
    <script src="/sps/js/libs/jquery/jquery.event.drag-2.0.js" type="text/javascript"></script>
    <script src="/sps/js/libs/jquery/jquery-ui-1.9.0.custom.js" type="text/javascript"></script>
    <script src="/sps/js/libs/jquery/jquery.ui.touch-punch.min.js " type="text/javascript"></script>
    <script src="/sps/js/libs/jquery/jquery.fileupload.js" type="text/javascript"></script>
    <script src="/sps/js/libs/jquery/jquery.i18n.properties-min-1.0.9.js" type="text/javascript"></script>
    <script src="/sps/js/libs/jquery/jquery.scrollTo.min.js" type="text/javascript"></script>
    <script src="/sps/js/libs/underscore/underscore-min.js" type="text/javascript"></script>
    <script src="/sps/js/libs/backbone/backbone-min.js" type="text/javascript"></script>
    <script src="/sps/js/libs/backbone/backgrid.js" type="text/javascript"></script>
    <script src="/sps/js/libs/backbone/backgrid-filter.js" type="text/javascript"></script>
    <script src="/sps/js/libs/mustache.js" type="text/javascript"></script>
    <script src="/sps/js/libs/bootstrap.js" type="text/javascript"></script>
    <script src="/sps/js/libs/bootstrap-tooltip.js" type="text/javascript"></script>
    <script src="/sps/js/libs/slick.core.js" type="text/javascript"></script>
    <script src="/sps/js/libs/slick_plugins/slick.cellrangeselector.js" type="text/javascript"></script>
    <script src="/sps/js/libs/slick_plugins/slick.cellselectionmodel.js" type="text/javascript"></script>
    <script src="/sps/js/libs/slick_plugins/slick.rowselectionmodel.js" type="text/javascript"></script>
    <script src="/sps/js/libs/slick_plugins/slick.rowmovemanager.js" type="text/javascript"></script>
    <script src="/sps/js/libs/slick.formatters.js" type="text/javascript"></script>
    <script src="/sps/js/libs/slick.editors.js" type="text/javascript"></script>
    <script src="/sps/js/libs/slick.grid.js" type="text/javascript"></script>
    <script src="/sps/js/libs/slick.dataview.js" type="text/javascript"></script>
    <script src="/sps/js/libs/http_treemenu/TreeMenu.js" type="text/javascript"></script>
    <script src="/sps/js/libs/scriptaculous/lib/prototype.js" type="text/javascript"></script>
    <script src="/sps/js/libs/scriptaculous/src/scriptaculous.js" type="text/javascript"></script>
    <script src="/sps/js/libs/scriptaculous/src/select.js" type="text/javascript"></script>
    <script src="/sps/js/libs/tableorderer.js" type="text/javascript"></script>
    <script src="/sps/js/libs/popup.js" type="text/javascript"></script>
    <script src="/sps/js/libs/timeframe.js" type="text/javascript"></script>
    <script src="/sps/js/libs/modalbox.js" type="text/javascript"></script>

    <script src="/sps/js/libs/sps/sps.js" type="text/javascript"></script>
    <script src="/sps/js/libs/sps/ao.js" type="text/javascript"></script>
    <script src="/sps/js/libs/sps/printer.js" type="text/javascript"></script>
    <script src="/sps/js/libs/sps/process.js" type="text/javascript"></script>
    <script src="/sps/js/libs/sps/store.js" type="text/javascript"></script>
    <script src="/sps/js/libs/sps/crf.js" type="text/javascript"></script>
    <script src="/sps/js/libs/sps/inventory.js" type="text/javascript"></script>
    <script src="/sps/js/libs/sps/inventory/Inventory.js" type="text/javascript"></script>
    <script src="/sps/js/libs/d3.v3.js" type="text/javascript"></script>
    <script src="/sps/js/libs/d3.csv.js" type="text/javascript"></script>
    <script src="/sps/js/libs/sps/tracking/Shipments.js" type="text/javascript"></script>
    <script src="/sps/js/libs/sps/roles.js" type="text/javascript"></script>
    <script src="/sps/js/libs/sps/analytics.js" type="text/javascript"></script>
    <script src="/sps/js/libs/sps/redcap.js" type="text/javascript"></script>
    <script src="/sps/js/libs/sps/dfunk.js" type="text/javascript"></script>
    <?php }?>



    <?php
    //TODO: remove this.. it's probably redundant
    if (!$require && $sps->task && file_exists('js/libs/sps/'.$sps->task.'.js')) {
        print '<script src="js/libs/sps/'.$sps->task.'.js" type="text/javascript"></script>';
    }
    ?>


  <body ng-app="myApp">
<div class="container">
<?php
//   <div id='foof'  ng-app="myApp" class="no-js">
//           <div ng-view></div>
//   
//           <div>AngularJS + RequireJS seed app: v<span app-version></span></div>
//</div>
?>

<?php print topMenu();?>
<div class="mini-layout fluid">
    <div class="mini-layout-sidebar lcolumn">
    <div class="lbuttons">
  <?php
if ($sps->task && file_exists($GLOBALS['root_dir'] . '/www/tasks/menu/'.$sps->task.'.php')) {

        include($GLOBALS['root_dir'] . '/www/tasks/menu/'.$sps->task.'.php');
} else {
       include($GLOBALS['root_dir'] . '/www/tasks/menu/default.php');
}
?>
  </div>
  </div>
  <div class="container pull-left">
  <div id="taskcontainer">
  <?php       
    if (!($sps->active_study)) {
        print "<h2>select study</h2>";
    } else if (!$sps->task) {
        print "<h2>select task</h2>";
    }
  if (!$require && ($sps->task) && file_exists($GLOBALS['root_dir'] . '/www/tasks/'.$sps->task.'.php')) {
        print '<div id="actioncontainer"></div>';
        print '<div id="staticcontainer">';
        include($GLOBALS['root_dir'] . '/www/tasks/'.$sps->task . '.php');
        print '</div>';
  }
  ?>
  </div>
</div>
</div>
</div>
<?php if(!$require) {
?>
  <script>sps = new Sps(); sps.initialize();</script>
<?php
}
?>
  </body>
</html>
