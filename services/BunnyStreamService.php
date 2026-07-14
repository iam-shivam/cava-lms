<?php
// services/BunnyStreamService.php

class BunnyStreamService {
    private $libraryId;
    private $apiKey;
    private $cdnHostname;

    public function __construct() {
        // Use constants defined in config.php if available, otherwise check $_ENV
        $this->libraryId = defined('BUNNY_STREAM_LIBRARY_ID') && BUNNY_STREAM_LIBRARY_ID !== '' ? BUNNY_STREAM_LIBRARY_ID : ($_ENV['BUNNY_STREAM_LIBRARY_ID'] ?? '');
        $this->apiKey = defined('BUNNY_STREAM_API_KEY') && BUNNY_STREAM_API_KEY !== '' ? BUNNY_STREAM_API_KEY : ($_ENV['BUNNY_STREAM_API_KEY'] ?? '');
        $this->cdnHostname = defined('BUNNY_STREAM_CDN_HOSTNAME') && BUNNY_STREAM_CDN_HOSTNAME !== '' ? BUNNY_STREAM_CDN_HOSTNAME : ($_ENV['BUNNY_STREAM_CDN_HOSTNAME'] ?? '');
    }

    /**
     * Create a video in Bunny Stream library.
     * Returns the GUID of the created video, or throws an Exception on failure.
     */
    public function createVideo($title) {
        if (empty($this->libraryId) || empty($this->apiKey)) {
            throw new Exception("Bunny Stream credentials are not configured in environment settings.");
        }

        $url = "https://video.bunnycdn.com/library/" . $this->libraryId . "/videos";
        $headers = [
            "AccessKey: " . $this->apiKey,
            "Content-Type: application/json",
            "Accept: application/json"
        ];
        $body = json_encode(["title" => $title]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            throw new Exception("cURL Error while creating Bunny video slot: " . $error_msg);
        }
        curl_close($ch);

        $data = json_decode($response, true);
        if ($httpCode !== 200 || !isset($data['guid'])) {
            $message = isset($data['message']) ? $data['message'] : "Unknown API Error";
            throw new Exception("Bunny API Error (HTTP $httpCode): " . $message);
        }

        return $data['guid'];
    }

    /**
     * Upload a video binary file to Bunny Stream.
     * Returns true on success, or throws an Exception on failure.
     */
    public function uploadVideo($videoId, $filePath) {
        if (empty($this->libraryId) || empty($this->apiKey)) {
            throw new Exception("Bunny Stream credentials are not configured in environment settings.");
        }

        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new Exception("Video file not found or not readable: " . $filePath);
        }

        $url = "https://video.bunnycdn.com/library/" . $this->libraryId . "/videos/" . $videoId;
        $headers = [
            "AccessKey: " . $this->apiKey,
            "Accept: application/json"
        ];

        $fileStream = fopen($filePath, 'r');
        $fileSize = filesize($filePath);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_PUT, true);
        curl_setopt($ch, CURLOPT_INFILE, $fileStream);
        curl_setopt($ch, CURLOPT_INFILESIZE, $fileSize);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            fclose($fileStream);
            throw new Exception("cURL Error while uploading video data: " . $error_msg);
        }

        curl_close($ch);
        fclose($fileStream);

        $data = json_decode($response, true);
        if ($httpCode !== 200 || (isset($data['success']) && !$data['success'])) {
            $message = isset($data['message']) ? $data['message'] : "Upload failed.";
            throw new Exception("Bunny API Error (HTTP $httpCode): " . $message);
        }

        return true;
    }

    /**
     * Delete a video in Bunny Stream library.
     */
    public function deleteVideo($videoId) {
        if (empty($this->libraryId) || empty($this->apiKey) || empty($videoId)) {
            return false;
        }

        $url = "https://video.bunnycdn.com/library/" . $this->libraryId . "/videos/" . $videoId;
        $headers = [
            "AccessKey: " . $this->apiKey,
            "Accept: application/json"
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode === 200;
    }

    /**
     * Get video details and status.
     * Status mapping:
     * 0: Created, 1: Uploaded, 2: Processing, 3: Transcoding, 4: Finished (Ready), 5: Error, 6: UploadFailed
     */
    public function getVideoStatus($videoId) {
        if (empty($this->libraryId) || empty($this->apiKey) || empty($videoId)) {
            return "Failed";
        }

        $url = "https://video.bunnycdn.com/library/" . $this->libraryId . "/videos/" . $videoId;
        $headers = [
            "AccessKey: " . $this->apiKey,
            "Accept: application/json"
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return "Failed";
        }

        $data = json_decode($response, true);
        if (!isset($data['status'])) {
            return "Failed";
        }

        $statusInt = intval($data['status']);
        switch ($statusInt) {
            case 0: return "Created";
            case 1: return "Uploading";
            case 2: return "Processing";
            case 3: return "Processing"; // Transcoding is also processing for the UI
            case 4: return "Ready";
            case 5: return "Failed";
            case 6: return "Failed";
            default: return "Processing";
        }
    }

    /**
     * Get the player embed/iframe URL.
     */
    public function getPlaybackUrl($videoId) {
        // Bunny Stream player embeds must use the standard player domain (iframe.mediadelivery.net)
        // direct pull zone hostnames (ending in .b-cdn.net) are for raw stream asset delivery.
        return "https://iframe.mediadelivery.net/embed/" . $this->libraryId . "/" . $videoId;
    }
}
