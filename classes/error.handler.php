<?php

class ErrorHandler
{
  public function getResponse($success, $status, $message, $extra = [])
  {
    $response = [
      'success' => $success,
      'status' => $status,
      'message' => $message
    ];
    if (!empty($extra)) {
      foreach ($extra as $key => $value) {
        $response[$key] = $value;
      }
    }
    return $response;
  }
}