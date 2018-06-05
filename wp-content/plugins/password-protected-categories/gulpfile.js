var gulp = require( 'gulp' ),
	pump = require( 'pump' ),
	jshint = require( 'gulp-jshint' ),
	minify = require( 'gulp-uglify' ),
	cleancss = require( 'gulp-clean-css' ),
	fs = require( 'fs' ),
	header = require( 'gulp-header' ),
	rename = require( 'gulp-rename' ),
	run = require( 'gulp-run' ),
	debug = require( 'gulp-debug' ),
	checktextdomain = require( 'gulp-checktextdomain' ),
	sftp = require( 'gulp-sftp' );

const pluginSlug = 'password-protected-categories';
const zipFile = pluginSlug + '.zip';
const pluginArchive = '/Users/andy/Dropbox/Barn2 Media/Plugins/Plugin archive/';
const readmeDir = '/Users/andy/Documents/localhost/barn2/wp-content/uploads/plugin-readme/';

var getVersion = function() {
	var readme = fs.readFileSync( 'readme.txt', 'utf8' );
	var version = readme.match( /Stable tag\:\s(.*)\s/i );
	return ( 1 in version ) ? version[1] : false;
};

var getCopyright = function() {
	return fs.readFileSync( 'copyright.txt' );
};

gulp.task( 'scripts', function( cb ) {
	pump( [
		gulp.src( ['assets/js/*.js', 'assets/js/admin/*.js', '!**/*.min.js'], { base: './' } ),
		debug(),
		header( getCopyright(), { 'version': getVersion() } ),
		minify( { compress: { negate_iife: false }, output: { comments: '/^\/*!/' } } ),
		rename( { suffix: '.min' } ),
		gulp.dest( '.' )
	], cb );
} );

gulp.task( 'styles', function( cb ) {
	pump( [
		gulp.src( ['assets/css/*.css', '!**/*.min.css'], { base: './' } ),
		debug(),
		header( getCopyright(), { 'version': getVersion() } ),
		cleancss( { compatibility: 'ie9', rebase: false } ),
		rename( { suffix: '.min' } ),
		gulp.dest( '.' )
	], cb );
} );

gulp.task( 'lint', function() {
	return gulp.src( ['assets/js/*.js', 'assets/js/admin/*.js', '!**/*.min.js'] )
		.pipe( jshint() )
		.pipe( jshint.reporter() ); // Dump results
} );

gulp.task( 'textdomain', function() {
	return gulp
		.src( ['**/*.php', '!**/EDD_SL_Plugin_Updater.php'] )
		.pipe(
			checktextdomain( {
				text_domain: pluginSlug,
				keywords: [
					'__:1,2d',
					'_e:1,2d',
					'_x:1,2c,3d',
					'esc_html__:1,2d',
					'esc_html_e:1,2d',
					'esc_html_x:1,2c,3d',
					'esc_attr__:1,2d',
					'esc_attr_e:1,2d',
					'esc_attr_x:1,2c,3d',
					'_ex:1,2c,3d',
					'_n:1,2,4d',
					'_nx:1,2,4c,5d',
					'_n_noop:1,2,3d',
					'_nx_noop:1,2,3c,4d'
				]
			} )
			);
} );

gulp.task( 'zip', ['scripts', 'styles'], function() {
	var zipCommand = `cd .. && rm ${zipFile}; zip -r ${zipFile} ${pluginSlug} -x "*/vendor/*" "*/node_modules/*" *.git* "*.DS_Store" */package*.json *gulpfile.js *copyright.txt`;

	return run( zipCommand ).exec();
} );

gulp.task( 'archive', ['zip'], function() {
	var deployDir = pluginArchive + pluginSlug + '/' + getVersion();

	if ( !fs.existsSync( deployDir ) ) {
		fs.mkdirSync( deployDir );
	}

	return gulp.src( zipFile, { cwd: '../' } )
		.pipe( debug() )
		.pipe( gulp.dest( deployDir ) );
} );

gulp.task( 'readme', function() {
	return gulp.src( 'readme.txt' )
		.pipe( gulp.dest( readmeDir + pluginSlug ) ) // copy to barn2 local site
		.pipe( sftp( { // upload to live
			host: 'barn2media.sftp.wpengine.com',
			user: 'barn2media',
			pass: 'n8PwMO9UU5fw1q7N',
			port: '2222',
			remotePath: '/wp-content/uploads/plugin-readme/' + pluginSlug
		} ) );
	/*.pipe( sftp( { // upload to staging
	 host: 'barn2media.sftp.wpengine.com',
	 user: 'barn2media-staging',
	 pass: 'qeZ7PbxuyPrS',
	 port: '2222',
	 remotePath: '/wp-content/uploads/plugin-readme/' + pluginSlug
	 } ) );*/
} );

gulp.task( 'changelog', function() {
	fs.readFileSync( 'copyright.txt' );
} );

gulp.task( 'build', ['scripts', 'styles', 'lint', 'textdomain', 'zip'] );
gulp.task( 'release', ['build', 'archive'] );
gulp.task( 'release-deploy', ['release', 'readme'] );
gulp.task( 'default', ['build'] );