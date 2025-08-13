<?php
class Google_Sheets_Service {
    public function send_data($data, $settings) {
        if (empty($settings['spreadsheet_id']) || 
            empty($settings['sheet_name']) || 
            empty($settings['oauth_credentials_path'])) {
            error_log('Google Sheets credentials missing');
            return false;
        }

        error_log("Prepare to send...");
        
        // Load Google API client
        require_once GOOGLE_AUTOMATION_PATH . 'google-api-php-client/vendor/autoload.php';
        putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $settings['oauth_credentials_path']);
        
        try {
            $client = new Google_Client();
            $client->useApplicationDefaultCredentials();
            $client->addScope(Google_Service_Sheets::SPREADSHEETS);
            $service = new Google_Service_Sheets($client);
            
            // Prepare values in order of parameters
            $values = [array_values($data)];
            
            $body = new Google_Service_Sheets_ValueRange([
                'values' => $values
            ]);
            
            $range = $settings['sheet_name'] . '!A:Z';
            
            $result = $service->spreadsheets_values->append(
                $settings['spreadsheet_id'],
                $range,
                $body,
                ['valueInputOption' => 'RAW']
            );
            
            return $result;
        } catch (Exception $e) {
            error_log('Google Sheets API Error: ' . $e->getMessage());
            return false;
        }
    }
}