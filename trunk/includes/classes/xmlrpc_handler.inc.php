<?php

class xmlrpc_handler {
	var $conntype = "";
	var $host =	"";
	var $port =	0;
	var $timeout =	10;
	var $request =	null;
	var $response =	null;
	var $calls =	0;
	var $mcalls =	0;
	var $times =	0;
	var $mtimes =	0;
	var $user = 	null;
	var $password =	null;
	var $errors =	array();

	function __construct( $address, $timeout ) {
		$this->conntype = "";
		$this->host = "";
		$this->port = 0;
		if ( preg_match( "|^unix://(.+)$|", $address, $match ) ) {
			if ( empty( $match[1] ) ) {
				$this->errors[] = "xmlrpc_socket_empty";
			} elseif ( !file_exists( $match[1] ) ) {
				$this->errors[] = "xmlrpc_socket_noexist";
			} elseif ( ( $perms = @fileperms( $match[1] ) ) === false ) {
				$this->errors[] = "xmlrpc_socket_noperms";
			} elseif ( ( $perms & 0xC000 ) !== 0xC000 ) {
				$this->errors[] = "xmlrpc_socket_nosocket";
			} elseif ( !is_writable( $match[1] ) ) {
				$this->errors[] = "xmlrpc_socket_nowrite";
			} elseif ( !is_readable( $match[1] ) ) {
				$this->errors[] = "xmlrpc_socket_noread";
			} else {
				$this->conntype = "socket";
				$this->host = $match[0];
			}
		} elseif ( preg_match( "<^(http|https)://(.*)$>", $address, $match ) ) {
			if ( empty( $match[2] ) ) {
				$this->errors[] = "xmlrpc_url_empty";
			} else {
				$this->conntype = "http";
				$this->host = $match[0];
			}
		} elseif ( preg_match( "|^:(\d{1,5})$|", $address, $match ) ) {
			$this->conntype = "localport";
			$this->port = $match[1];
			settype( $this->port, "int" );
			if ( $this->port < 1 || $this->port > 65535 ) {
				$this->errors[] = "xmlrpc_localport_outofrange";
			} else {
				$this->host = "127.0.0.1";
			}
		} elseif ( preg_match( "|^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3}):(\d{1,5})$|", $address, $match ) ) {
			$this->conntype = "port";
			$this->host = "{$match[1]}.{$match[2]}.{$match[3]}.{$match[4]}";
			$this->port = $match[5];
			settype( $this->port, "int" );
			if ( ip2long( $this->host ) === false ) {
				$this->errors[] = "xmlrpc_port_invalidip";
			} elseif ( $this->host != long2ip( ip2long( $this->host ) ) ) {
				$this->errors[] = "xmlrpc_port_invalidipplus";
			}
			if ( $this->port < 1 || $this->port > 65535 ) {
				$this->errors[] = "xmlrpc_port_outofrange";
			}
		} else {
			$this->errors[] = "xmlrpc_nodestination";
		}

		$this->timeout	= $timeout;
	}

	function set_type( &$value, $xmlrpc_type ) {
		switch ( $xmlrpc_type ) {
			case "base64" : {
				$value = (object)$value;
				$value->xmlrpc_type = $xmlrpc_type;
				break;
			}
			case "datetime" : {
				$value = (object)$value;
				$value->xmlrpc_type = $xmlrpc_type;
				$value->timestamp = strtotime( $value->scalar );
				$value->timestamp = $value->timestamp === false ? -1 : $value->timestamp;
				break;
			}
		}
	}

	private function decode_rec( $inode ) {
		$ntype = $inode->nodeName;
		$nvalue = $inode->nodeValue;
		switch ( $ntype ) {
			case "string" : {
				$response = $nvalue;
				break;
			}
			case "i4" :
			case "int" : {
				$response = (int)$nvalue;
				break;
			}
			case "struct" : {
				$mnode = $inode->firstChild;
				while ( $mnode != NULL ) {
					if ( ( $mnode->firstChild->nodeValue == "faultCode" ) && ( $mnode->nextSibling->firstChild->nodeValue == "faultString" ) ) {
						$this->errors[] = "{$mnode->nextSibling->lastChild->nodeValue} ({$mnode->lastChild->nodeValue})";
						$this->errors[] = "xmlrpc_fault";
						return false;
					}
					$nname = $mnode->firstChild->nodeValue;
					$dec = $this->decode_rec( $mnode->lastChild->firstChild );
					if ( $dec === false ) {
						return false;
					}
					$response[$nname] = $dec;
					$mnode = $mnode->nextSibling;
				}
				break;
			}
			case "array" : {
				$vnode = $inode->firstChild->firstChild;
				while ( $vnode != NULL ) {
					$dec = $this->decode_rec( $vnode->firstChild );
					$response[] = $dec;
					if ( $dec === false ) {
						return false;
					}
					$vnode = $vnode->nextSibling;
				}
				break;
			}
		}

		return ( isset( $response ) ? $response : "" );
	}

	private function decode( $str ) {
		$str = preg_replace( "%<(/{0,1})(i8|ex\.i8)>%", "<\\1string>", $str );
		$str = preg_replace( "/>\s*?</s", "><", $str );
		$xml = new DOMDocument( "1.0", "utf-8" );
		$xml->formatOutput = true;
		if ( !@$xml->loadXML( $str ) ) {
			$this->errors[] = "xmlrpc_xml_noload";
			return false;
		}

		$root = $xml->firstChild;
		$root->nodeName;

		if ( $root->firstChild->nodeName == "fault" ) {
			$response[faultCode] = (int)$root->firstChild->firstChild->firstChild->firstChild->lastChild->firstChild->nodeValue;
			$response[faultString] = $root->firstChild->firstChild->firstChild->lastChild->lastChild->firstChild->nodeValue;
			$this->errors[] = "{$response[faultString]} ({$response[faultCode]})";
			$this->errors[] = "xmlrpc_fault";
			return false;
		} else {
			$vnode = $root->firstChild->firstChild->firstChild;
			$inode = $vnode->firstChild;
			$ntype = $inode->nodeName;
			$nvalue = $inode->nodeValue;
			switch ( $ntype ) {
				case "string" : {
					$response = $nvalue;
					break;
				}
				case "i4" :
				case "int" : {
					$response = (int)$nvalue;
					break;
				}
				case "struct" : {
					$mnode = $inode->firstChild;
					while ( $mnode != NULL ) {
						$nname = $mnode->firstChild->nodeValue;
						$dec = $this->decode_rec( $mnode->lastChild->firstChild );
						if ( $dec === false ) {
							return false;
						}
						$response[$nname] = $dec;
						$mnode = $mnode->nextSibling;
					}
				}
				case "array" : {
					$vnode = $inode->firstChild->firstChild;
					while ( $vnode != NULL ) {
						$dec = $this->decode_rec( $vnode->firstChild );
						if ( $dec === false ) {
							return false;
						}
						$response[] = $dec;
						$vnode = $vnode->nextSibling;
					}
				}
			}
		}

		return ( isset( $response ) ? $response : array() );
	}

	private function encode_request_rec( $method, $param, &$xml, $pnode ) {
		$vnode = $pnode->appendChild( $xml->createElement( "value" ) );
		switch ( gettype( $param ) ) {
			case "integer" : {
				$inode = $vnode->appendChild( $xml->createElement( "int", $param ) );
				break;
			}
			case "double" : {
				$inode = $vnode->appendChild( $xml->createElement( "double", $param ) );
				break;
			}
			case "string" : {
				$inode = $vnode->appendChild( $xml->createElement( "string", $param ) );
				break;
			}
			case "array" : {
				$assoc = false;
				foreach ( array_keys( $param ) as $key => $val ) {
					if ( !preg_match( "/^[0-9]*$/", $val ) ) {
						$assoc = true;
					}
				}
				if ( $assoc ) {
					$anode = $vnode->appendChild( $xml->createElement( "struct" ) );
					foreach ( $param as $key => $val ) {
						$mnode = $anode->appendChild( $xml->createElement( "member" ) );
						$nnode = $mnode->appendChild( $xml->createElement( "name", $key ) );
						if ( $this->encode_request_rec( $method, $val, $xml, $mnode ) === false ) {
							return false;
						}
					}
				} else {
					$anode = $vnode->appendChild( $xml->createElement( "array" ) );
					$dnode = $anode->appendChild( $xml->createElement( "data" ) );
					foreach ( $param as $key => $val ) {
						if ( $this->encode_request_rec( $method, $val, $xml, $dnode ) === false ) {
							return false;
						}
					}
				}
				break;
			}
			case "object" : {
				switch ( $param->xmlrpc_type ) {
					case "base64" : {
						$inode = $vnode->appendChild( $xml->createElement( "base64", base64_encode( $param->scalar ) ) );
						break;
					}
					case "datetime" : {
						$inode = $vnode->appendChild( $xml->createElement( "datetime", $param->timestamp ) );
						break;
					}
				}
				break;
			}
			default : {
				$this->errors[] = gettype( $param );
				$this->errors[] = "xmlrpc_request_invalidtype";
				return false;
				break;
			}
		}
	}

	private function encode_request( $method, $params ) {
		$xml = new DOMDocument( "1.0", "utf-8" );
		$xml->formatOutput = true;

		$root = $xml->appendChild( $xml->createElement( "methodCall" ) );
		$mnode = $root->appendChild( $xml->createElement( "methodName", $method ) );
		
		$psnode = $root->appendChild( $xml->createElement( "params" ) );
		if ( is_array( $params ) ) {
			$assoc = false;
			foreach ( array_keys( $params ) as $key => $val ) {
				if ( !preg_match( "/^[0-9]*$/", $val ) ) {
					$assoc = true;
				}
			}
			if ( $assoc ) {
				$pnode = $psnode->appendChild( $xml->createElement( "param" ) );
				$vnode = $pnode->appendChild( $xml->createElement( "value" ) );
				$anode = $vnode->appendChild( $xml->createElement( "struct" ) );
				foreach ( $params as $key => $val ) {
					$mnode = $anode->appendChild( $xml->createElement( "member" ) );
					$nnode = $mnode->appendChild( $xml->createElement( "name", $key ) );
					if ( $this->encode_request_rec( $method, $val, $xml, $mnode ) === false ) {
						return false;
					}
				}
			} else {
				foreach ( $params as $key => $val ) {
					$pnode = $psnode->appendChild( $xml->createElement( "param" ) );
					if ( $this->encode_request_rec( $method, $val, $xml, $pnode ) === false ) {
						return false;
					}
				}
			}
		} else {
			$pnode = $psnode->appendChild( $xml->createElement( "param" ) );
			if ( $this->encode_request_rec( $method, $params, $xml, $pnode ) === false ) {
				return false;
			}
		}

		return $xml->saveXML();
	}

	function getconntype() {
		return $this->conntype;
	}

	function setaccount( $user, $password ) {
		$this->user = $user;
		$this->password = $password;
	}

	function setmrequest( $methods, $params = array() ) {
		$this->request = array();
		foreach ( $methods as $methodkey => $methodval ) {
			$this->request[] = array( "methodName" => $methodval, "params" => $params );
		}
		$this->request = $this->encode_request( "system.multicall", array( $this->request ) );
		if ( $this->request === false ) {
			return false;
		} else {
			return true;
		}
	}

	function setumrequest( $methods, $params ) {
		$this->request = array();
		$num = count( $methods );
		for ( $i = 0; $i < $num; $i++ ) {
			$this->request[] = array( "methodName" => $methods[$i], "params" => $params[$i] );
		}
		$this->request = $this->encode_request( "system.multicall", array( $this->request ) );
		if ( $this->request === false ) {
			return false;
		} else {
			return true;
		}
	}

	function setrequest( $method, $attributes ) {
		$this->request = $this->encode_request( $method, $attributes );
		if ( $this->request === false ) {
			return false;
		} else {
			return true;
		}
	}

	function scgi_call() {
		$st = getmicrotime();

		$len = strlen( $this->request );
		$headers = "CONTENT_LENGTH\0{$len}\0";
		$headers .= "SCGI\01\0";
		$len = strlen( $headers );
		$out = "{$len}:{$headers},{$this->request}";

		$fp = @fsockopen( $this->host, $this->port, $errno, $errstr, $this->timeout );
		if ( $fp === false ) {
			$this->errors[] = "{$error} ({$errno})";
			$this->errors[] = "xmlrpc_scgi_connectfailed";
			$tt = getmicrotime();
			$this->calls++;
			$this->times = $this->times + $tt - $st;

			return false;
		} else {
			fwrite( $fp, $out );
			do {
				$line = fgets( $fp );
			} while ( trim( $line ) != "" );

			$this->response = stream_get_contents( $fp );
			if ( $this->response === false ) {
				$this->errors[] = "xmlrpc_scgi_readfailed";
				$tt = getmicrotime();
				$this->calls++;
				$this->times = $this->times + $tt - $st;

				return false;
			} elseif ( $this->response === "" ) {
				$this->errors[] = "xmlrpc_scgi_emptystring";
				$tt = getmicrotime();
				$this->calls++;
				$this->times = $this->times + $tt - $st;

				return false;
			} else {
				$tt = getmicrotime();
				$this->calls++;
				$this->times = $this->times + $tt - $st;

				return true;
			}
		}
	}

	function gw_call() {
		$st = getmicrotime();

		$len = strlen( $this->request );
		$headers = "CONTENT_LENGTH\0{$len}\0";
		$headers .= "SCGI\01\0";
		$len = strlen( $headers );
		$out = "{$len}:{$headers},{$this->request}";

		$ch = curl_init( $this->host );
		//curl_setopt( $ch, CURLOPT_HEADER, true );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_POST, true );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $this->request );
		curl_setopt( $ch, CURLOPT_USERPWD, "{$this->user}:{$this->password}" );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
		$this->response = curl_exec( $ch ); 
		if ( $this->response === false ) {
			$error = curl_error( $ch );
			$errno = curl_errno( $ch );
			$this->errors[] = "{$error} ({$errno})";
			$this->errors[] = "xmlrpc_gw_connectfailed";
			return false;
		} elseif ( $this->response === "" ) {
			$error = curl_error( $ch );
			$errno = curl_errno( $ch );
			$this->errors[] = "{$error} ({$errno})";
			$this->errors[] = "xmlrpc_gw_empttstring";
			return false;
		}
		$tt = getmicrotime();
		$this->calls++;
		$this->times = $this->times + $tt - $st;

		return true;
	}

	function call(){
		switch ( $this->conntype ) {
			case "" : {
				return false;

				break;
			}
			case "http" :
			case "https" : {
				return $this->gw_call();

				break;
			}
			case "port" :
			case "localport" :
			case "socket" : {
				return $this->scgi_call();

				break;
			}
			default : {
				return false;

				break;
			}
		}
	}

	function parse() {
		$this->response = $this->decode( $this->response );
		if ( $this->response !== false ) {
			return true;
		} else {
			return false;
		}
	}

	function mfetch( $methods ) {
		$r = -1;
		$responses = array();
		foreach ( $methods as $methodkey => $methodval ) {
			if ( $methodval[1] == "." ) {
				$prefix = substr( $methodval, 0, 1 );
				$methodval = substr( $methodval, 5 );
				$methodval = "{$prefix}{$methodval}";
			} elseif ( strlen( $methodval ) > 6 && $methodval[6] == "." ) {
				$prefix = substr( $methodval, 0, 1 );
				$methodval = substr( $methodval, 7 );
				$methodval = "{$methodval}";
			} elseif ( $methodval == "view_list" ) {
			} elseif ( substr( $methodval, 0, 3 ) == "dht" ) {
			} else {
				$methodval = substr( $methodval, 4 );
			}
			$r++;

			if ( $methodval == "dht_statistics" ) {
				foreach ( $this->response[$r][0] as $dkey => $dval ) {
					$responses["{$methodval}_{$dkey}"] = $dval;
				}
			} else {
				$responses[$methodval] = $this->response[$r][0];
			}
		}

		return $responses;
	}

	function fetch() {
		return $this->response;
	}

	function clearerrors() {
		$this->errors = array();
	}

	function geterrorsnum() {
		return count( $this->errors );
	}

	function geterrors() {
		return $this->errors;
	}

	function getlasterror() {
		return array_pop( $this->errors );
	}

}

?>
