<?php

/**
 * MercadoPago cURL RestClient
 */
class Eloom_MercadoPago_RestClient {

  const API_BASE_URL = "https://api.mercadopago.com";

  private static function build_request($request) {
    if (!extension_loaded("curl")) {
      throw new Eloom_MercadoPago_Exception("cURL extension not found. You need to enable cURL in your php.ini or another configuration you have.");
    }

    if (!isset($request["method"])) {
      throw new Eloom_MercadoPago_Exception("No HTTP METHOD specified");
    }

    if (!isset($request["uri"])) {
      throw new Eloom_MercadoPago_Exception("No URI specified");
    }

    // Set headers
    $headers = array("accept: application/json");
    $json_content = true;
    $form_content = false;
    $default_content_type = true;

    if (isset($request["headers"]) && is_array($request["headers"])) {
      foreach ($request["headers"] as $h => $v) {
        $h = strtolower($h);
        $v = strtolower($v);

        if ($h == "content-type") {
          $default_content_type = false;
          $json_content = $v == "application/json";
          $form_content = $v == "application/x-www-form-urlencoded";
        }

        array_push($headers, $h . ": " . $v);
      }
    }
    if ($default_content_type) {
      array_push($headers, "content-type: application/json");
    }

    // Build $connect
    $connect = curl_init();

    curl_setopt($connect, CURLOPT_USERAGENT, "MercadoPago PHP SDK v" . Eloom_MercadoPago_Api::version);
    curl_setopt($connect, CURLOPT_RETURNTRANSFER, true);
    //curl_setopt($connect, CURLOPT_SSL_VERIFYPEER, true);
    //curl_setopt($connect, CURLOPT_CAINFO, dirname(__FILE__) . "/cacert.pem");
    curl_setopt($connect, CURLOPT_CUSTOMREQUEST, $request["method"]);
    curl_setopt($connect, CURLOPT_HTTPHEADER, $headers);

    // Set parameters and url
    if (isset($request["params"]) && is_array($request["params"]) && count($request["params"]) > 0) {
      $request["uri"] .= (strpos($request["uri"], "?") === false) ? "?" : "&";
      $request["uri"] .= self::build_query($request["params"]);
    }
    curl_setopt($connect, CURLOPT_URL, self::API_BASE_URL . $request["uri"]);

    // Set data
    if (isset($request["data"])) {
      if ($json_content) {
        if (gettype($request["data"]) == "string") {
          json_decode($request["data"], true);
        } else {
          $request["data"] = json_encode($request["data"]);
        }

        if (function_exists('json_last_error')) {
          $json_error = json_last_error();
          if ($json_error != JSON_ERROR_NONE) {
            throw new Eloom_MercadoPago_Exception("JSON Error [{$json_error}] - Data: " . $request["data"]);
          }
        }
      } else if ($form_content) {
        $request["data"] = self::build_query($request["data"]);
      }

      curl_setopt($connect, CURLOPT_POSTFIELDS, $request["data"]);
    }

    return $connect;
  }

  private static function exec($request) {
    // private static function exec($method, $uri, $data, $content_type) {

    $connect = self::build_request($request);

    $api_result = curl_exec($connect);
    $api_http_code = curl_getinfo($connect, CURLINFO_HTTP_CODE);

    if ($api_result === FALSE) {
      throw new Eloom_MercadoPago_Exception(curl_error($connect));
    }
    $response = array(
        "status" => $api_http_code,
        "response" => json_decode($api_result, true)
    );

    if ($response['status'] >= 400) {
      $message = $response['response']['message'];
      $code = $response['status'];
      if (isset($response['response']['cause'])) {
        if (isset($response['response']['cause']['code']) && isset($response['response']['cause']['description'])) {
          //$message .= " - " . $response['response']['cause']['code'] . ': ' . $response['response']['cause']['description'];
          $code = $response['response']['cause']['code'];
          $message = $response['response']['cause']['description'];
        } else if (is_array($response['response']['cause'])) {
          foreach ($response['response']['cause'] as $cause) {
            //$message .= " - " . $cause['code'] . ': ' . $cause['description'];
            $code = $cause['code'];
            $message = $cause['description'];
            break;
          }
        }
      }

      throw new Eloom_MercadoPago_Exception($message, $code);
    }

    curl_close($connect);

    return $response;
  }

  private static function build_query($params) {
    if (function_exists("http_build_query")) {
      return http_build_query($params, "", "&");
    } else {
      foreach ($params as $name => $value) {
        $elements[] = "{$name}=" . urlencode($value);
      }

      return implode("&", $elements);
    }
  }

  public static function get($request) {
    $request["method"] = "GET";

    return self::exec($request);
  }

  public static function post($request) {
    $request["method"] = "POST";

    return self::exec($request);
  }

  public static function put($request) {
    $request["method"] = "PUT";

    return self::exec($request);
  }

  public static function delete($request) {
    $request["method"] = "DELETE";

    return self::exec($request);
  }

}
