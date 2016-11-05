<?php
session_start();

require_once('FitbitClient.class.php');

if (isset($_GET['logout'])) {
   session_destroy();
   session_start();
}

if ((!isset($_SESSION['authFitbit']) || $_SESSION['authFitbit'] != 1) && isset($_REQUEST['code'])) {
   // Callback
   $fclient = new FitbitClient($_REQUEST['code']);
   $_SESSION['parameters'] = $fclient->getParameters();
   $_SESSION['authFitbit'] = 1;
}

if ((!isset($_SESSION['authFitbit']) || $_SESSION['authFitbit'] != 1)) {
   // First connection
   FitbitClient::getAuthorizationCode();
}

if (isset($_SESSION['authFitbit']) && $_SESSION['authFitbit'] == 1) {
   // Standard mode
   $fclient = new FitbitClient();
   $fclient->setParameters($_SESSION['parameters']);
   print_r($fclient->getUserProfile());
   echo '<br />';
   print_r($fclient->getHeartRateIntraday());
}

?>