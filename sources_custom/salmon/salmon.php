<?php
/**
 * Copyright 2009 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

require_code('salmon/webfinger');

/**
 * Determines whether the current element being parsed has a parent with 
 * the given name.
 * @param array $atom An entry from xml_parse_into_struct.
 * @param string $parent The parent element's name we are checking for.
 * @param array $breadcrumbs An array of element names showing the current
 *     parse tree.
 * @return boolean True if the atom's parent's name is equal to the value
 *     of $parent.
 */
function salmon_parent_is($atom, $parent, $breadcrumbs) {
	return ($breadcrumbs[$atom['level'] - 1] == $parent);     
}


/**
 * Converts an ATOM encoded Salmon post to a SalmonEntry.
 * @param string $atom_string The raw POST to the Salmon endpoint.
 * @return SalmonEntry An object representing the information in the POST.
 */
function salmon_from_atom($atom_string) {
	$xml_parser = xml_parser_create(''); 
	$xml_values = array();
	$xml_tags = array();
	if(!$xml_parser) 
		return false; 
	xml_parser_set_option($xml_parser, XML_OPTION_TARGET_ENCODING, 'UTF-8'); 
	xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, 0); 
	xml_parser_set_option($xml_parser, XML_OPTION_SKIP_WHITE, 1); 
	xml_parse_into_struct($xml_parser, trim($atom_string), $xml_values); 
	xml_parser_free($xml_parser); 

	$entry = new SalmonEntry();
	$breadcrumbs = array();
	for ($i = 0; in_array($i,array_keys($xml_values)); $i++) {
		$atom = $xml_values[$i];
		// Only process one entry.  This could be generalized to a feed later.
		if (strtolower($atom['tag']) == 'entry' && 
			strtolower($atom['type']) == 'close') {
			break;
		}
		// Keep a "breadcrumb" list of the tag hierarchy we're currently in.
		$breadcrumbs[$atom['level']] = $atom['tag'];
      
		// Parse individual attributes one at a time.
		switch (strtolower($atom['tag'])) {
			case 'id':
				$entry->id = $atom['value'];
				break;
			case 'name':
				if (salmon_parent_is($atom, 'author', $breadcrumbs)) {
					$entry->author_name = $atom['value'];
				}
				break;
			case 'uri':
				if (salmon_parent_is($atom, 'author', $breadcrumbs)) {
					$entry->author_uri = $atom['value'];
				}
				break; 
			case 'thr:in-reply-to':
				$entry->thr_in_reply_to = $atom['value'];
				break;
			case 'content':
				$entry->content = $atom['value'];
				break;
			case 'title':
				$entry->title = $atom['value'];
				break;
			case 'updated':
				$entry->updated = $atom['value'];
				break;
			case 'sal:signature':
				$entry->salmon_signature = $atom['value'];
				break;
		}
	}
    
	$entry->webfinger = from_acct_string($entry->author_uri);
	return $entry;    
}

/**
 * Represents a single Salmon entry retrieved from a post to the Salmon
 * endpoint.
 */
class SalmonEntry {
	public $id;
	public $author_name;
	public $author_uri;
	public $thr_in_reply_to;
	public $content;
	public $title;
	public $updated;
	public $salmon_signature;
	public $webfinger;
  
	/**
	 * Determines whether this SalmonEntry's signature is valid.
	 * @return boolean True if the signature can be validated, False otherwise.
	 */
	public function validate() {
		return false;
	}
  
	/**
	 * Posts this salmon to the site.
	 */
	public function post_as_comment($type,$id,$url,$self_title) {
		$time = strtotime($this->updated); 
    
		$email = '';
		// Pulls user data
		if ($this->webfinger !== false) {
			$email = $this->webfinger->get_email();
			if (method_exists($GLOBALS['FORUM_DRIVER'], 'get_member_from_email_address'))
			{
				$uid = $GLOBALS['FORUM_DRIVER']->get_member_from_email_address($email);
			}
		}
		$this->do_salmon_as_comment($type,$id,$url,$self_title,$this->title,$this->content,$email,$this->author_name,NULL,NULL);
	}
	
	/**
	 * Add comments to the specified resource.
	 *
	 * @param  ID_TEXT		The type (download, etc) that this commenting is for
	 * @param  ID_TEXT		The ID of the type that this commenting is for
	 * @param  mixed			The URL to where the commenting will pass back to (to put into the comment topic header) (URLPATH or Tempcode)
	 * @param  ?string		The title to where the commenting will pass back to (to put into the comment topic header) (NULL: don't know, but not first post so not important)
	 * @param  ?string		The name of the forum to use (NULL: default comment forum)
	 * @param  ?BINARY		Whether the post is validated (NULL: unknown, find whether it needs to be marked unvalidated initially). This only works with the OCF driver (hence is the last parameter).
	 * @return boolean		Whether a hidden post has been made
	 */
	function do_salmon_as_comment($module,$id,$self_url,$self_title,$title=NULL,$post=NULL,$email='',$poster_name_if_guest='',$forum=NULL,$validated=NULL)
	{
		if (!is_null($post)) $_POST['post'] = $post;
		if (!is_null($title)) $_POST['title'] = $title;
		$_POST['email'] = $email;
		$_POST['poster_name_if_guest'] = $poster_name_if_guest;
		
		return do_comments(true,$module,$id,$self_url,$self_title,$forum,true,$validated,false,true);
	}
}

/**
 * Get the resource discovery links, for dumping into a feed's header.
 *
 * @param  string		The type of resource this should be for (default null)
 * @param  string		The ID of this specific resource (default null)
 */
function salmon_get_xrd_discovery_links($type=null, $id=null) {
  $url = salmon_generate_api_url($type,$id);
  $to_return = "<Link rel='salmon' href='.$url.'/>\n";
  $to_return .= "<Link rel='http://salmon-protocol.org/ns/salmon-replies' href='$url' />\n";
  $to_return .= "<Link rel='http://salmon-protocol.org/ns/salmon-mention' href='$url' />\n";
  return $to_return;
}

function salmon_add_xrd_crypt_keys($user) {
  $sig = get_public_sig($user);
  $encoded = magic_sig_to_string($sig);

  $to_return = '<Link rel="magic-public-key" href="data:application/magic-public-key,'.$encoded.'"/>';

  $to_return .= '<Property xmlns:mk="http://salmon-protocol.org/ns/magic-key"
                  type="http://salmon-protocol.org/ns/magic-key"
                  mk:key_id="1">
          '.$encoded.'
        </Property>';
  return $to_return;
}

/**
 * Find the URL to which Salmon should be posted. This is the "salmon
 * endpoint", which should be unique for each feed. Thus you should
 * pass in enough information to identify your feed as exactly as you
 * can, to ensure that you only receive the salmon that's directed for
 * you, and that your salmon don't appear for anyone else. The given
 * parameters allow you to specify your resource in whichever way you
 * like. The recommended way is to treat them like the parameters of
 * the functions in 'feedback.php'. They are dumped straight into the
 * endpoint URL as GET parameters "type" and "id", although no
 * encoding is done to enforce this (eg. you could, if really needed,
 * send in a parameter as '&foo=bar&baz=foobar').
 * 
 * @param  string		The "type" of the content your feed is for (equivalent to the 'type' passed to the functions in feedback.php). Default null.
 * @param  string		The "id" of the content your feed is for (equivalent to the 'id' passed to the functions in feedback.php, or can be defined to conform to any other ). Default null.
 * @return string		The URL to the requested salmon endpoint.
 */
function salmon_generate_api_url($salmon_for_type=null,$id=null) {
  $url = get_base_url();
  $url .= "/data_custom/salmon.php";

  if (!is_null($salmon_for_type)) {
    $url .= "&type=".$salmon_for_type;
  }
  if (!is_null($id)) {
    $url .= "&id=".$id;
  }

  return $url;
}

/**
 * Gets the link pointing to the salmon endpoint to a syndicated feed.
 */
function salmon_get_feed_link() {
  return "<link rel='salmon' href='".salmon_generate_api_url()."'/>";
}

/**
 * Attempts to parse data sent to the Salmon endpoint and post it as a
 * comment for the specified resource.
 */
function parse_salmon_post($type, $id, $url, $title) {
	require_code('salmon/magicsig');
  // Allow cross domain JavaScript requests, from salmon-playground.
  if (strtoupper($_SERVER['REQUEST_METHOD']) == "OPTIONS" &&
      strtoupper($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']) == "POST") {
    // See https://developer.mozilla.org/En/HTTP_access_control
    header('HTTP/1.1 200 OK');
    header('Access-Control-Allow-Origin: * ');
    die();
  }

  //TODO(kurrik): Check that this always works, even if always_populate_raw_post_data is Off
  $request_body = @file_get_contents('php://input');
  $array = magic_sig_parse($request_body);
 
  $entry = salmon_from_atom($array['data']);

  // Validate the request if the option is set.
  //if (get_option('salmon_validate')) {
    /*if ($entry->validate() === false) {
      header('HTTP/1.1 403 Forbidden');
      print "The posted Salmon entry's signature did not validate.";
      die();
    }*/
  //}
    $entry->post_as_comment($type,$id,$url,$title);

    header('HTTP/1.1 201 Created');
    print "The Salmon entry was posted.";
  //}
  die();
}
