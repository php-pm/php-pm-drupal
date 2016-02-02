<?php

/**
 * @file
 * Contains \PHPPM\Bridges\DrupalKernel.
 */

namespace PHPPM\Bridges;

use PHPPM\Bridges\BridgeInterface;
use PHPPM\Bridges\HttpKernel as SymfonyBridge;
use React\Http\Request as ReactRequest;
use React\Http\Response as ReactResponse;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpFoundation\StreamedResponse as SymfonyStreamedResponse;
use Symfony\Component\HttpKernel\TerminableInterface;


/**
 * PHP-PM bridge adapter for DrupalKernel.
 *
 * Extends `\PHPPM\Bridges\HttpKernel` to populate various request
 * meta-variables specified by CGI/1.1 (RFC 3875).
 *
 * @see http://www.faqs.org/rfcs/rfc3875.html
 * @see http://php.net/manual/en/reserved.variables.server.php
 */
class DrupalKernel extends SymfonyBridge implements BridgeInterface {

  /**
   * Handle a request using a HttpKernelInterface implementing application.
   *
   * @param \React\Http\Request $request
   * @param \React\Http\Response $response
   */
  public function onRequest(ReactRequest $request, ReactResponse $response)
  {
      if (null === $this->application) {
          return;
      }

      $content = '';
      $headers = $request->getHeaders();
      $contentLength = isset($headers['Content-Length']) ? (int) $headers['Content-Length'] : 0;

      $request->on('data', function($data)
          use ($request, $response, &$content, $contentLength)
      {
          // read data (may be empty for GET request)
          $content .= $data;

          // handle request after receive
          if (strlen($content) >= $contentLength) {
              $syRequest = self::mapRequest($request, $content);

              try {
                  $syResponse = $this->application->handle($syRequest);
              } catch (\Exception $exception) {
                  $response->writeHead(500); // internal server error
                  $response->end();
                  return;
              }

              self::mapResponse($response, $syResponse);

              if ($this->application instanceof TerminableInterface) {
                  $this->application->terminate($syRequest, $syResponse);
              }
          }
      });
  }

  /**
   * Convert React\Http\Request to Symfony\Component\HttpFoundation\Request
   *
   * @param ReactRequest $reactRequest
   * @return SymfonyRequest $syRequest
   */
  protected static function mapRequest(ReactRequest $reactRequest, $content)
  {
      $method = $reactRequest->getMethod();
      $headers = $reactRequest->getHeaders();
      $query = $reactRequest->getQuery();
      $post = array();

      // parse body?
      if (isset($headers['Content-Type']) && (0 === strpos($headers['Content-Type'], 'application/x-www-form-urlencoded'))
          && in_array(strtoupper($method), array('POST', 'PUT', 'DELETE', 'PATCH'))
      ) {
          parse_str($content, $post);
      }

      $cookies = array();
      if (isset($headers['Cookie'])) {
        $headersCookie = explode(';', $headers['Cookie']);
        foreach ($headersCookie as $cookie) {
          list($name, $value) = explode('=', trim($cookie));
          $cookies[$name] = $value;
        }
      }

      $parameters =
        in_array(strtoupper($method), array('POST', 'PUT', 'DELETE', 'PATCH'))
        ? $post
        : $query;
      $syRequest = SymfonyRequest::create(
          // $uri, $method, $parameters, $cookies, $files, $server, $content
          $reactRequest->getPath(),
          $method,
          $parameters,
          $cookies,
          array(),
          array(),
          $content
      );
      $syRequest->headers->replace($headers);

      // Set CGI/1.1 (RFC 3875) server vars.
      if (empty($_ENV)) {
        // In some cases with cli, $_ENV isn't set, so get with getenv().
        // @todo: Make this more efficient to eliminate running per request.
        // Static variable?
        $_ENV['DOCUMENT_ROOT'] = getenv('DOCUMENT_ROOT');
        $_ENV['SCRIPT_NAME'] = getenv('SCRIPT_NAME');
      }
      $serverVars = array_merge(
      	$syRequest->server->all(),
        array(
          'DOCUMENT_ROOT' => $_ENV['DOCUMENT_ROOT'],
          'GATEWAY_INTERFACE' => 'CGI/1.1',
          'SCRIPT_NAME' => $_ENV['SCRIPT_NAME'],
          // SCRIPT_FILENAME contains the name of the php-pm startup script.
          // Must override here.
          'SCRIPT_FILENAME' => $_ENV['DOCUMENT_ROOT'] . $_ENV['SCRIPT_NAME'],
        )
      );
      $syRequest->server->replace($serverVars);

      return $syRequest;
  }


  /**
   * Convert Symfony\Component\HttpFoundation\Response to React\Http\Response
   *
   * @param ReactResponse $reactResponse
   * @param SymfonyResponse $syResponse
   */
  protected static function mapResponse(ReactResponse $reactResponse,
      SymfonyResponse $syResponse)
  {
      $headers = $syResponse->headers->all();
      $reactResponse->writeHead($syResponse->getStatusCode(), $headers);

      // @TODO convert StreamedResponse in an async manner
      if ($syResponse instanceof SymfonyStreamedResponse) {
          ob_start();
          $syResponse->sendContent();
          $content = ob_get_contents();
          ob_end_clean();
      }
      else {
          $content = $syResponse->getContent();
      }

      $reactResponse->end($content);
  }

}
