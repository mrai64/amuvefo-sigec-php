<?php
/**
 *	nomefile: database-handler.php
 *	funzione: creare un oggetto connessione mysql, che sarà propagato
 *	          al php seguente per eseguire operazioni su un solo database
 */
// impostazione errori riportati
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

/**
 * $con connessione mysql procedurale 
 */
// $con = mysqli_connect("31.11.39.113:3306", "Sql1515403", "3o860s7no2", "Sql1515403_4");

$con = mysqli_connect( DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if (!$con){
	return 'Connessione non riuscita, info: ' . mysqli_connect_error() ;
	exit(0);
}
// impostazione caratteri usati
mysqli_set_charset($con, DB_CHARSET); // utf8 multibyte da 4 https://dev.mysql.com/doc/refman/8.0/en/charset-unicode-utf8mb4.html 

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
if (!defined('AUTH_KEY')){
	define( 'AUTH_KEY',         'l /rXXKX^8]qhDjWCHO+Y:5V*.tT@e9r]bJd2I>^X}y{H?H0jOhKJ/=^zYAI#Xj3' );
	define( 'SECURE_AUTH_KEY',  'w?rmS{-FwVXq2n>eJw&:UaY;5+L?aKMc5**U,6u&nbY0xz:z(rgH,&G]U:yzzq^R' );
	define( 'LOGGED_IN_KEY',    'zC !?UJ=Utz|p.DPC)<yH6>8-<e-JA{!aXvpr_-p3yYA3Z!tO8ytP,}zzn<2z,Vt' );
	define( 'NONCE_KEY',        'T1qTk}KFoSA/iNmx{Zu7gcqNh;=_t}QSktq|E^7nLk`g,3Bz$Du=(;p ]~.yFE{G' );
	define( 'AUTH_SALT',        'dcv#,| a3f4eO75mkh&0l3rtDa$ytX8;<`s]qS @$v+ETO1VrtBFp>&[WSw{yA*b' );
	define( 'SECURE_AUTH_SALT', 'K06uUm4<([m`5`Zr3hX[!kdObuWx<?eQB Mw5#P&0z<<xFB@,ymsW7~,NZQpC4a)' );
	define( 'LOGGED_IN_SALT',   '3h!+q!QqP&Hj`DCctr5S:!,NJP-P38lgOtQe[AQfi@4s(bRgy~D$w2cYyirGQA/L' );
	define( 'NONCE_SALT',       'S&^>bo6e`Mx~FpNwFoEgXhJFz`c6y@v5UR)cVqd7Gf1Qgd|^5.19@+Vl~GyXqqTL' );
}

/**#@-*/
