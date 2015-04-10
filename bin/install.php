<?php
// (C) Campbell Software Solutions 2015
// Portions (C) 2006-2013 osTicket

//Configure settings from environmental variables
$_SERVER['HTTP_ACCEPT_LANGUAGE'] = getenv("LANGUAGE") ?: "en-us";

$vars = array(
  'name'      => getenv("INSTALL_NAME")  ?: 'My Helpdesk',
  'email'     => getenv("INSTALL_EMAIL") ?: 'helpdesk@example.com',

  'fname'       => getenv("INSTALL_FIRSTNAME") ?: 'Admin',
  'lname'       => getenv("INSTALL_LASTNAME")  ?: 'User',
  'admin_email' => getenv("INSTALL_EMAIL")     ?: 'admin@example.com',
  'username'    => getenv("INSTALL_USERNAME")  ?: 'ostadmin',
  'passwd'      => getenv("INSTALL_PASSWORD")  ?: 'Admin1',
  'passwd2'     => getenv("INSTALL_PASSWORD")  ?: 'Admin1',

  'prefix'   => getenv("MYSQL_PREFIX")              ?: 'ost_',
  'dbhost'   => getenv("MYSQL_PORT_3306_TCP_ADDR"),
  'dbname'   => getenv("MYSQL_DATABASE")            ?: 'osticket',
  'dbuser'   => getenv("MYSQL_USER")                ?: 'osticket',
  'dbpass'   => getenv("MYSQL_PASSWORD")            ?: getenv("MYSQL_ENV_MYSQL_PASSWORD"),

  'siri'     => getenv("INSTALL_SECRET"),
  'config'   => getenv("INSTALL_CONFIG") ?: '/data/upload/include/ost-sampleconfig.php'
);

//Script settings
define('CONNECTION_TIMEOUT_SEC', 180);

function err( $msg) {
  fwrite(STDERR, "$msg\n");
  exit(1);
}

//Check mandatory settings provided
if (!isset($vars['dbhost'])) {
  err('Missing required environmental variable: MYSQL_HOST');
}
if (!isset($vars['dbpass'])) {
  err('Missing required environmental variable: MYSQL_PASSWORD');
}

//Require files
chdir("/data/upload/setup_hidden");
require "/data/upload/setup_hidden/setup.inc.php";
require_once INC_DIR.'class.installer.php';

//Create installer class
define('OSTICKET_CONFIGFILE','/data/upload/include/ost-config.php');
$installer = new Installer(OSTICKET_CONFIGFILE); //Installer instance.

//Wait for database connection
echo "Waiting for database TCP connection to become available...\n";
$s = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
$t = 0;
while (!@socket_connect($s,$vars['dbhost'],3306) && $t < CONNECTION_TIMEOUT_SEC) {
  $t++;
  if (($t % 15) == 0) {
    echo "Waited for $t seconds...\n";
  }
  sleep(1);
}
if ($t >= CONNECTION_TIMEOUT_SEC) {
  err("Timed out waiting for database TCP connection");
}

//Check database installation status
$db_installed = false;
echo "Connecting to database mysql://${vars['dbuser']}@${vars['dbhost']}/${vars['dbname']}\n";
if (!db_connect($vars['dbhost'],$vars['dbuser'],$vars['dbpass']))
   err(sprintf(__('Unable to connect to MySQL server: %s'), db_connect_error()));
elseif(explode('.', db_version()) < explode('.', $installer->getMySQLVersion()))
   err(sprintf(__('osTicket requires MySQL %s or later!'),$installer->getMySQLVersion()));
elseif(!db_select_database($vars['dbname']) && !db_create_database($vars['dbname'])) {
   err("Database doesn't exist");
} elseif(!db_select_database($vars['dbname'])) {
   err('Unable to select the database');
} else {
   $sql = 'SELECT * FROM `'.$vars['prefix'].'config` LIMIT 1';
   if(db_query($sql, false)) {
       $db_installed = true;
       echo "Database already installed\n";
   }
}

//Create secret if not set by env var and not previously stored
DEFINE('SECRET_FILE','/data/secret.txt');
if (!$vars['siri']) {
  if (file_exists(SECRET_FILE)) {
    echo "Loading installation secret\n";
    $vars['siri'] = file_get_contents(SECRET_FILE);
  } else {
    echo "Generating new installation secret and saving\n";
    //Note that this randomly generated value is not intended to secure production sites!
    $vars['siri'] = substr(str_shuffle("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ01234567890_="), 0, 32);
    file_put_contents(SECRET_FILE, $vars['siri']);
  }
} else {
  echo "Using installation secret from INSTALL_SECRET environmental variable\n";
}

//Always rewrite config file in case MySQL details changed (e.g. ip address)
echo "Updating configuration file\n";
if (!$configFile = file_get_contents($vars['config'])) {
  err("Failed to load configuration file: {$vars['config']}");
};
$configFile= str_replace("define('OSTINSTALLED',FALSE);","define('OSTINSTALLED',TRUE);",$configFile);
$configFile= str_replace('%ADMIN-EMAIL',$vars['admin_email'],$configFile);
$configFile= str_replace('%CONFIG-DBHOST',$vars['dbhost'],$configFile);
$configFile= str_replace('%CONFIG-DBNAME',$vars['dbname'],$configFile);
$configFile= str_replace('%CONFIG-DBUSER',$vars['dbuser'],$configFile);
$configFile= str_replace('%CONFIG-DBPASS',$vars['dbpass'],$configFile);
$configFile= str_replace('%CONFIG-PREFIX',$vars['prefix'],$configFile);
$configFile= str_replace('%CONFIG-SIRI',$vars['siri'],$configFile);

if (!file_put_contents($installer->getConfigFile(), $configFile)) {
   err("Failed to write configuration file");
}

//Perform database installation if required
if (!$db_installed) {
  echo "Installing database. Please wait...\n";
  if (!$installer->install($vars)) {
    $errors=$installer->getErrors();
    echo "Database installation failed. Errors:\n";
    foreach($errors as $e) {
      echo "  $e\n";
    }
    exit(1);
  } else {
    echo "Database installation successful\n";
  }
}

?>
