<?php
/*
Plugin Name: PDF List
Plugin URI: http://PluginHomePage.com
Description: Plugin description in a few words or more
Version: Version number such as 2.3
Author: Your Name (if you wrote it)
Author URI: http://YourHomePage.com
License: GPL (or whatever license terms you choose)
*/

// WPGetpdflist -> get list from WP post table in database
class WPGetpdflist
{

	public static $pdfs = array();
	
	public static function returnpdflist()
	{

		$query_pdfs_args = array(
		    'post_type' => 'attachment', //any
		    
		    //'post_mime_type' =>'application/pdf', //pfds only
		    //'post_mime_type' =>'application/msword', //documents only
		    //'post_mime_type' => 'none' //everything
		    
		    'post_mime_type' =>'application', 
		    
		    'post_status' => 'inherit', 
		    'posts_per_page' => -1,
		    
		);
			
		try {
			$query_pdfs = new WP_Query( $query_pdfs_args );			
		} catch (Exception $e) {
			exit();
		}
		
		
		foreach ( $query_pdfs->posts as $pdf) {
			$pdfs[]= array(			
					$pdf->guid, 
					$pdf->post_title, 
					$pdf->post_modified_gmt				
				);	   
		}
		
		wp_reset_query();
	
		
		return $pdfs;
		
	}
	
}

//WPlistpdfs-> adds shortcode to list files in a WP HTML page or post
class WPHTMLlistpdfs 
{

    public function __construct()
    {
        add_shortcode('listpdfs', array($this, 'shortcode_listpdfs_func'));
    }
     
	public function shortcode_listpdfs_func()
	{
		
		$pdflist = new WPGetpdflist();
		
		foreach ( $pdflist->returnpdflist() as $pdf) {
			echo '<p><a href="' . $pdf[0] . '" target="blank" itemprop="url" alt="' . $pdf[1] . '" >' . $pdf[1] . '</a></p>';	
		}
	
	}
}
$WPHTMLlistpdfs = new WPHTMLlistpdfs();



//WPXMLlistpdf-> list applications as an XML page
class WPXMLlistpdf
{

    public function __construct()
    {        
	add_action( 'template_redirect', array($this, 'XMLPage_listpdfs_func'));
    }
     
	public function XMLPage_listpdfs_func()
	{
	if ( ! preg_match( '/pdf-sitemap\.xml$/', $_SERVER['REQUEST_URI'] ) ) {
	    return;
	  }

	  header( "HTTP/1.1 200 OK" );
	  header( 'X-Robots-Tag: noindex, follow', true );
	  header( 'Content-Type: text/xml' );
	  echo '<?xml version="1.0" encoding="' . get_bloginfo( 'charset' ) . '"?>' . "\n";
	  echo '<!-- generator="' . home_url( '/' ) . '" -->' . "\n";
	  $xml  = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">' . "\n";
	  
	  $pdflist = new WPGetpdflist();
		
	 foreach ( $pdflist->returnpdflist() as $pdf) {

	    if ( ! empty( $pdf[0] ) ) {
	      $xml .= "\t<url>\n";
	      $xml .= "\t\t<loc>" . $pdf[0] . "</loc>\n";
	      $xml .= "\t\t<lastmod>" . mysql2date( 'Y-m-d\TH:i:s+00:00', $pdf[2], false ) . "</lastmod>\n";
	      $xml .= "\t\t<changefreq>" . 'weekly' . "</changefreq>\n";
	      $xml .= "\t\t<priority>" . '0.7' . "</priority>\n";
	      $xml .= "\t</url>\n";
	    }
	    
	  }
	  
	  $xml .= '</urlset>';
	  echo ( "$xml" );
	  exit();	
				
		
	}
}
 
$WPXMLlistpdf = new WPXMLlistpdf();




?>