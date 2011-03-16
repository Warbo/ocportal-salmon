<?php
require_once 'Crypt/RSA.php';

define( 'MAGIC_SIG_NS', 'http://salmon-protocol.org/ns/magic-env');

function get_public_sig($user_id) {
    // FIXME: Use custom profile fields
    if ($sig = get_ocp_cpf('magic_sig_public_key',$user_id)) {
      return $sig[0];
    } else {
      magic_sig_generate_key_pair($user_id);

      // FIXME: We can't put arrays into custom profile fields can we?
      // Let's find out what the values here are meant to be, find their
      // domain and implode them together with something outside this
      // domain, so that we can explode them apart here.
      $sig = get_ocp_cpf('magic_sig_public_key',$user_id);
      return $sig[0];
    }
}

//Generates the pair keys
function generate_key_pair($user_id) {
    $rsa = new Crypt_RSA();

    $keypair = $rsa->createKey();

	// FIXME: Should we put 'ocf_' before these?
    set_custom_field($user_id,'magic_sig_public_key',$keypair['publickey']);
    set_custom_field($user_id,'magic_sig_private_key',$keypair['privatekey']);
}

function base64_url_encode($input) {
    return strtr(base64_encode($input), '+/', '-_');
}

function base64_url_decode($input) {
    return base64_decode(strtr($input, '-_', '+/'));
}

function to_string($key) {
    $public_key = new Crypt_RSA();
    $public_key->loadKey($key, CRYPT_RSA_PRIVATE_FORMAT_PKCS1);

    $mod = magic_sig_base64_url_encode($public_key->modulus->toBytes());
    $exp = magic_sig_base64_url_encode($public_key->exponent->toBytes());

    return 'RSA.' . $mod . '.' . $exp;
}

function magic_sig_parse($text) {
    $dom = DOMDocument::loadXML($text);
    return magic_sig_from_dom($dom);
}

function magic_sig_from_dom($dom) {
    $env_element = $dom->getElementsByTagNameNS(MAGIC_SIG_NS, 'env')->item(0);
    if (!$env_element) {
      $env_element = $dom->getElementsByTagNameNS(MAGIC_SIG_NS, 'provenance')->item(0);
    }

    if (!$env_element) {
      return false;
    }

    $data_element = $env_element->getElementsByTagNameNS(MAGIC_SIG_NS, 'data')->item(0);
    $sig_element = $env_element->getElementsByTagNameNS(MAGIC_SIG_NS, 'sig')->item(0);
    return array(
      'data' => base64_url_decode(preg_replace('/\s/', '', $data_element->nodeValue)),
      'data_type' => $data_element->getAttribute('type'),
      'encoding' => $env_element->getElementsByTagNameNS(MAGIC_SIG_NS, 'encoding')->item(0)->nodeValue,
      'alg' => $env_element->getElementsByTagNameNS(MAGIC_SIG_NS, 'alg')->item(0)->nodeValue,
      'sig' => preg_replace('/\s/', '', $sig_element->nodeValue),
    );
}

?>
