<?php
require __DIR__ . '/vendor/autoload.php';

define('CREDENTIALS_PATH', __DIR__.'/credentials/');
/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 */

class GSheetsHandler{
    public function __construct($client=null)
    {
        if( !isset($client)) {
            $client = GSheetsHandler::getClient();
        }
        $this->client = $client;
    }

    public static function getClient()
    {
        $client = new Google_Client();
        $client->setApplicationName('Google Sheets API PHP Quickstart');
        $client->setScopes(Google_Service_Sheets::SPREADSHEETS);
        $client->setAuthConfig(CREDENTIALS_PATH.'credentials.json');
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');

        // Load previously authorized token from a file, if it exists.
        // The file token.json stores the user's access and refresh tokens, and is
        // created automatically when the authorization flow completes for the first
        // time.
        $tokenPath = CREDENTIALS_PATH.'token.json';
        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $client->setAccessToken($accessToken);
        }

        // If there is no previous token or it's expired.
        if ($client->isAccessTokenExpired()) {
            // Refresh the token if possible, else fetch a new one.
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            } else {
                // Request authorization from the user.
                $authUrl = $client->createAuthUrl();
                printf("Open the following link in your browser:\n%s\n", $authUrl);
                print 'Enter verification code: ';
                $authCode = trim(fgets(STDIN));

                // Exchange authorization code for an access token.
                $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
                $client->setAccessToken($accessToken);

                // Check to see if there was an error.
                if (array_key_exists('error', $accessToken)) {
                    throw new Exception(join(', ', $accessToken));
                }
            }
            // Save the token to a file.
            if (!file_exists(dirname($tokenPath))) {
                mkdir(dirname($tokenPath), 0700, true);
            }
            file_put_contents($tokenPath, json_encode($client->getAccessToken()));
        }
        return $client;
    }

    public function writeToSpreadSheet($spreadsheet_id, $range, $values){
        $service = new Google_Service_Sheets($this->client);
        $requestBody = new Google_Service_Sheets_ValueRange(["values" => [$values]]);
        $params = [
            'valueInputOption' => 'RAW',
            'insertDataOption' => 'INSERT_ROWS',
            'includeValuesInResponse' => true
        ];
        try {
            $response = $service->spreadsheets_values->append($spreadsheet_id, $range, $requestBody, $params);
            $this->debug('Ok\n');
        }
        catch (Exception $exception){
            $this->debug($exception);
        }
    }

    public function debug($text){
        $file = fopen(__DIR__.'/debug.txt', 'a+');
        fwrite($file, $text);
        fclose($file);
    }
}

