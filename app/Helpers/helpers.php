<?php

if (!function_exists('getProjectsJson')) {
    /**
     * Get the projects from the JSON file
     *
     * @return array
     */
    function getProjectsJson(): array
    {
        // Path to the JSON file
        $filePath = storage_path('app/projects.json');

        // Check if the file exists
        if (!file_exists($filePath)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        // Read the file contents
        $jsonContent = file_get_contents($filePath);

        // Decode the JSON content
        $data = json_decode($jsonContent, true);

        // Check for JSON parsing errors
        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['error' => 'Failed to decode JSON'], 500);
        }

        return $data;
    }
}
