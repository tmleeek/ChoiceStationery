<?php
class Minify_ClosureCompilerOnline {

    static public function minifyJs($js, $options = array())
    {
    	$ch = curl_init('http://closure-compiler.appspot.com/compile');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
		  'js_code' => $js,
		  'compilation_level' => 'SIMPLE_OPTIMIZATIONS',
		  'output_format' => 'text',
		  'output_info' => 'compiled_code'
		)));
		
		$minifiedJs = curl_exec($ch);
		if (curl_getinfo($ch, CURLINFO_HTTP_CODE) != 200) $minifiedJs = null;
		$minifiedJs = preg_replace('/^\s*\/\*.*?\*\//s', '', $minifiedJs, 1);
		return trim($minifiedJs);
    }
}

