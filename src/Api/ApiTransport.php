<?php

namespace Scp\Api;

/**
 * CURL API Transporter.
 */
class ApiTransport
implements ApiTransporter
{
    /**
     * Dispatch the request.
     *
     * @param string $method
     * @param string $url
     * @param array  $postData
     * @param array  $headers
     *
     * @return ApiResponse
     *
     * @throws ApiError
     * @throws ApiResponseError
     */
    public function call($method, $url, $postData, array $headers)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => 1,
        ));

        $body = curl_exec($curl);

        if (curl_errno($curl)) {
            throw new ApiError(curl_error($curl));
        }

        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        logActivity("URL : $url data" . serialize([$body]));
		
        $request = new ApiRequest($method, $url);

        return new ApiResponse($request, $body, $status);
    }
}
