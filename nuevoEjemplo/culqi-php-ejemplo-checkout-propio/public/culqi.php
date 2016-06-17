<?php
    define('CULQI_SDK_VERSION', '1.1.0'); class UrlAESCipher { protected $key; protected $cipher = MCRYPT_RIJNDAEL_128; protected $mode = MCRYPT_MODE_CBC; function __construct($spec63bf = null) { $this->setBase64Key($spec63bf); } public function setBase64Key($spec63bf) { $this->key = base64_decode($spec63bf); } private function sp42f8e2() { if ($this->key != null) { return true; } else { return false; } } private function sp4b5aaf() { return mcrypt_create_iv(16, MCRYPT_RAND); } public function urlBase64Encrypt($sp755f74) { if ($this->sp42f8e2()) { $sp6bc36d = mcrypt_get_block_size($this->cipher, $this->mode); $sp810031 = UrlAESCipher::pkcs5_pad($sp755f74, $sp6bc36d); $sp1c42c6 = $this->sp4b5aaf(); return trim(UrlAESCipher::base64_encode_url($sp1c42c6 . mcrypt_encrypt($this->cipher, $this->key, $sp810031, $this->mode, $sp1c42c6))); } else { throw new Exception('Invlid params!'); } } public function urlBase64Decrypt($sp755f74) { if ($this->sp42f8e2()) { $spcc5dfb = UrlAESCipher::base64_decode_url($sp755f74); $sp1c42c6 = substr($spcc5dfb, 0, 16); $sp20ef79 = substr($spcc5dfb, 16); return trim(UrlAESCipher::pkcs5_unpad(mcrypt_decrypt($this->cipher, $this->key, $sp20ef79, $this->mode, $sp1c42c6))); } else { throw new Exception('Invlid params!'); } } public static function pkcs5_pad($sp2dbdda, $sp6bc36d) { $spf187c8 = $sp6bc36d - strlen($sp2dbdda) % $sp6bc36d; return $sp2dbdda . str_repeat(chr($spf187c8), $spf187c8); } public static function pkcs5_unpad($sp2dbdda) { $spf187c8 = ord($sp2dbdda[strlen($sp2dbdda) - 1]); if ($spf187c8 > strlen($sp2dbdda)) { return false; } if (strspn($sp2dbdda, chr($spf187c8), strlen($sp2dbdda) - $spf187c8) != $spf187c8) { return false; } return substr($sp2dbdda, 0, -1 * $spf187c8); } protected function base64_encode_url($sp36c362) { return strtr(base64_encode($sp36c362), '+/', '-_'); } protected function base64_decode_url($sp36c362) { return base64_decode(strtr($sp36c362, '-_', '+/')); } } class Culqi { public static $llaveSecreta; public static $codigoComercio; public static $servidorBase = 'https://pago.culqi.com'; public static function cifrar($sp3231b0) { $spadece1 = new UrlAESCipher(); $spadece1->setBase64Key(Culqi::$llaveSecreta); return $spadece1->urlBase64Encrypt($sp3231b0); } public static function decifrar($sp3231b0) { $spadece1 = new UrlAESCipher(); $spadece1->setBase64Key(Culqi::$llaveSecreta); return $spadece1->urlBase64Decrypt($sp3231b0); } } class Pago { const URL_VALIDACION_AUTORIZACION = '/api/v1/web/crear/'; const URL_ANULACION = '/api/v1/devolver/'; const URL_CONSULTA = '/api/v1/consultar/'; const PARAM_COD_COMERCIO = 'codigo_comercio'; const PARAM_EXTRA = 'extra'; const PARAM_SDK_INFO = 'sdk'; const PARAM_NUM_PEDIDO = 'numero_pedido'; const PARAM_MONTO = 'monto'; const PARAM_MONEDA = 'moneda'; const PARAM_DESCRIPCION = 'descripcion'; const PARAM_COD_PAIS = 'cod_pais'; const PARAM_CIUDAD = 'ciudad'; const PARAM_DIRECCION = 'direccion'; const PARAM_NUM_TEL = 'num_tel'; const PARAM_INFO_VENTA = 'informacion_venta'; const PARAM_TICKET = 'ticket'; const PARAM_VIGENCIA = 'vigencia'; const PARAM_CORREO_ELECTRONICO = 'correo_electronico'; const PARAM_NOMBRES = 'nombres'; const PARAM_APELLIDOS = 'apellidos'; const PARAM_ID_USUARIO_COMERCIO = 'id_usuario_comercio'; private static function getSdkInfo() { return array('v' => CULQI_SDK_VERSION, 'lng_n' => 'php', 'lng_v' => phpversion(), 'os_n' => PHP_OS, 'os_v' => php_uname()); } public static function crearDatospago($sp821fb9, $sp37cd46 = null) { Pago::validateParams($sp821fb9); $sp327f8d = Pago::getCipherData($sp821fb9, $sp37cd46); $spa944fe = array(Pago::PARAM_COD_COMERCIO => Culqi::$codigoComercio, Pago::PARAM_INFO_VENTA => $sp327f8d); $spd4bb0a = Pago::validateAuth($spa944fe); if (!empty($spd4bb0a) && array_key_exists(Pago::PARAM_TICKET, $spd4bb0a)) { $spd3ecee = array(Pago::PARAM_COD_COMERCIO => $spd4bb0a[Pago::PARAM_COD_COMERCIO], Pago::PARAM_TICKET => $spd4bb0a[Pago::PARAM_TICKET]); $spd4bb0a[Pago::PARAM_INFO_VENTA] = Culqi::cifrar(json_encode($spd3ecee)); } return $spd4bb0a; } public static function consultar($sp517f27) { $sp327f8d = Pago::getCipherData(array(Pago::PARAM_TICKET => $sp517f27)); $sp821fb9 = array(Pago::PARAM_COD_COMERCIO => Culqi::$codigoComercio, Pago::PARAM_INFO_VENTA => $sp327f8d); return Pago::postJson(Culqi::$servidorBase . Pago::URL_CONSULTA, $sp821fb9); } public static function anular($sp517f27) { $sp327f8d = Pago::getCipherData(array(Pago::PARAM_TICKET => $sp517f27)); $sp821fb9 = array(Pago::PARAM_COD_COMERCIO => Culqi::$codigoComercio, Pago::PARAM_INFO_VENTA => $sp327f8d); return Pago::postJson(Culqi::$servidorBase . Pago::URL_ANULACION, $sp821fb9); } private static function getCipherData($sp821fb9, $sp37cd46 = null) { $sp4c1514 = array_merge(array(Pago::PARAM_COD_COMERCIO => Culqi::$codigoComercio), $sp821fb9); if (!empty($sp37cd46)) { $sp4c1514[Pago::PARAM_EXTRA] = $sp37cd46; } $sp4c1514[Pago::PARAM_SDK_INFO] = Pago::getSdkInfo(); $spe828da = json_encode($sp4c1514); return Culqi::cifrar($spe828da); } private static function validateAuth($sp821fb9) { return Pago::postJson(Culqi::$servidorBase . Pago::URL_VALIDACION_AUTORIZACION, $sp821fb9); } private static function validateParams($sp821fb9) { if (!isset($sp821fb9[Pago::PARAM_MONEDA]) or empty($sp821fb9[Pago::PARAM_MONEDA])) { throw new InvalidParamsException('[Error] Debe existir una moneda'); } else { if (strlen(trim($sp821fb9[Pago::PARAM_MONEDA])) != 3) { throw new InvalidParamsException('[Error] La moneda debe contener exactamente 3 caracteres.'); } } if (!isset($sp821fb9[Pago::PARAM_MONTO]) or empty($sp821fb9[Pago::PARAM_MONTO])) { throw new InvalidParamsException('[Error] Debe existir un monto'); } else { if (is_numeric($sp821fb9[Pago::PARAM_MONTO])) { if (!ctype_digit($sp821fb9[Pago::PARAM_MONTO])) { throw new InvalidParamsException('[Error] El monto debe ser un número entero, no flotante.'); } } else { throw new InvalidParamsException('[Error] El monto debe ser un número entero.'); } } } private static function postJson($sp954ec6, $sp821fb9) { $sp2523af = array('http' => array('header' => "Content-Type: application/json\r\n" . "User-Agent: php-context\r\n", 'method' => 'POST', 'content' => json_encode($sp821fb9), 'ignore_errors' => true)); $spc35bfb = stream_context_create($sp2523af); $spd4bb0a = file_get_contents($sp954ec6, false, $spc35bfb); $spbfb5b7 = Culqi::decifrar($spd4bb0a); return json_decode($spbfb5b7, true); } } class InvalidParamsException extends Exception { }
