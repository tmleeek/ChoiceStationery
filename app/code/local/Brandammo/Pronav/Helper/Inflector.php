<?php
/**
 * 
 */
/**
 * 
 * @author Carlo Tasca
 * @version 1
 *
 */
class Brandammo_Pronav_Helper_Inflector extends Mage_Core_Helper_Abstract {
	
	/**
	 * Convert string in format 'StringString' to 'string_string'
	 *
	 * @param  string $string  string to underscore
	 * @return string $string  underscored string
	 */
	public function underscore($string) {
		return str_replace(' ', '_', strtolower ( preg_replace ( '~(?<=\\w)([A-Z])~', '_$1', $string ) ));
	}
	
	/**
	 * Convert a word in to the format for a class name. Converts 'class_name' to 'ClassName'
	 *
	 * @param string  $word  Word to classify
	 * @return string $word  Classified word
	 */
	public function classify($word) {
		static $cache = array ();
		
		if (! isset ( $cache [$word] )) {
			$word = preg_replace ( '/[$]/', '', $word );
			$classify = preg_replace_callback ( '~(_?)([-_])([\w])~', array ("Brandammo_Pronav_Helper_Inflector", "classifyCallback" ), ucfirst ( strtolower ( $word ) ) );
			$cache [$word] = $classify;
		}
		return $cache [$word];
	}
	
	/**
	 * Callback function to classify a classname properly.
	 *
	 * @param  array  $matches  An array of matches from a pcre_replace call
	 * @return string $string   A string with matches 1 and mathces 3 in upper case.
	 */
	public static function classifyCallback($matches) {
		return $matches [1] . strtoupper ( $matches [3] );
	}
	
	public function humanize($str) {
		
		$str = trim ( strtolower ( $str ) );
		$str = str_replace ( '_', ' ', $str );
		return ucwords($str);
	}
}

/* End of file */

