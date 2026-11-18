<?php


namespace App\Services;

use App\Models\SmsGateway;



class Arkesel
{
    protected $apiKey;
    protected $sender;

    /**
     * If no credentials are provided, fetch the active gateway from DB.
     *
     * @param string|null $apiKey
     * @param string|null $sender
     */
    public function __construct($apiKey = null, $sender = null)
    {
        if ($apiKey && $sender) {
            $this->apiKey = $apiKey;
            $this->sender = $sender;
        } else {
            $gateway = SmsGateway::where('active', 1)->first();
            if (!$gateway) {
                throw new \Exception('No active SMS gateway found.');
            }
            $this->apiKey = $gateway->key;
            $this->sender = $gateway->sender;
        }
    }

    /**
     * Send SMS via Arkesel
     */
    public function send($message, $recipients, $options = [])
    {
        $postFields = [
            'sender' => $this->sender,
            'message' => $message,
            'recipients' => $recipients,
        ];
        if (!empty($options['scheduled_date'])) {
            $postFields['scheduled_date'] = $options['scheduled_date'];
        }
        if (!empty($options['callback_url'])) {
            $postFields['callback_url'] = $options['callback_url'];
        }
        if (!empty($options['use_case'])) {
            $postFields['use_case'] = $options['use_case'];
        }

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://sms.arkesel.com/api/v2/sms/send',
            CURLOPT_HTTPHEADER => ['api-key: ' . $this->apiKey],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query($postFields),
        ]);
        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);
        \Log::info('Arkesel send response: ' . $response);
        if ($error) {
            throw new \Exception('Arkesel SMS Error: ' . $error);
        }
        // Handle Arkesel API error in response
        $responseData = json_decode($response, true);
        if (isset($responseData['status']) && $responseData['status'] === 'error') {
            throw new \Exception('Arkesel API Error: ' . ($responseData['message'] ?? 'Unknown error'));
        }
        return $response;
    }

    /**
     * Send Template SMS via Arkesel
     * @param string $message
     * @param array $recipients (associative: phone => [fields])
     * @param array $options ['scheduled_date', 'callback_url', 'use_case']
     * @return mixed
     */
    public function sendTemplate($message, $recipients, $options = [])
    {
        $payload = [
            'sender' => $this->sender,
            'message' => $message,
            'recipients' => $recipients,
        ];
        if (!empty($options['scheduled_date'])) {
            $payload['scheduled_date'] = $options['scheduled_date'];
        }
        if (!empty($options['callback_url'])) {
            $payload['callback_url'] = $options['callback_url'];
        }
        if (!empty($options['use_case'])) {
            $payload['use_case'] = $options['use_case'];
        }

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://sms.arkesel.com/api/v2/sms/template/send',
            CURLOPT_HTTPHEADER => [
                'api-key: ' . $this->apiKey,
                'Content-Type: application/json'
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($payload),
        ]);
        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);
        if ($error) {
            throw new \Exception('Arkesel Template SMS Error: ' . $error);
        }
        return $response;
    }

    /**
     * Get SMS balance from Arkesel
     * @return mixed
     */
    public function getBalance()
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://sms.arkesel.com/api/v2/clients/balance-details',
            CURLOPT_HTTPHEADER => ['api-key: ' . $this->apiKey],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ]);
        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);
        if ($error) {
            throw new \Exception('Arkesel Balance Error: ' . $error);
        }
        return $response;
    }

    /**
     * Get SMS message status by message ID
     */
    public function getMessageStatus($msgId)
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://sms.arkesel.com/api/v2/sms/' . $msgId,
            CURLOPT_HTTPHEADER => ['api-key: ' . $this->apiKey],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ]);
        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);
        if ($error) {
            throw new \Exception('Arkesel Message Status Error: ' . $error);
        }
        return $response;
    }

    /**
     * Get message delivery reports for multiple message IDs
     */
    public function getMessageReports($msgIds)
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://sms.arkesel.com/api/v2/sms/message-reports',
            CURLOPT_HTTPHEADER => ['api-key: ' . $this->apiKey],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query(['msg_ids' => $msgIds]),
        ]);
        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);
        if ($error) {
            throw new \Exception('Arkesel Message Reports Error: ' . $error);
        }
        return $response;
    }

    /**
     * Create a contact group
     */
    public function createContactGroup($groupName)
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://sms.arkesel.com/api/v2/contacts/groups',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query(['group_name' => $groupName]),
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'api-key: ' . $this->apiKey
            ],
        ]);
        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);
               \Log::info('Arkesel create group response: ' . $response);
        // Check for cURL errors

        if ($error) {
            throw new \Exception('Arkesel SMS Error: ' . $error);
        }
        // Handle Arkesel API error in response
        $responseData = json_decode($response, true);
        if (isset($responseData['status']) && $responseData['status'] === 'error') {
            throw new \Exception('Arkesel API Error: ' . ($responseData['message'] ?? 'Unknown error'));
        }
        if ($error) {
            throw new \Exception('Arkesel Create Group Error: ' . $error);
        }
        return $response;
    }

        /**
     * Add contacts to a group
     */
    public function addContacts($groupName, $contacts)
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://sms.arkesel.com/api/v2/contacts',
            CURLOPT_HTTPHEADER => ['api-key: ' . $this->apiKey],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query([
                'group_name' => $groupName,
                'contacts' => $contacts
            ]),
        ]);
        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);
        if ($error) {
            throw new \Exception('Arkesel Add Contacts Error: ' . $error);
        }
        return $response;
    }

        /**
     * Send SMS to a contact group
     */
    public function sendToContactGroup($sender, $message, $groupName)
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://sms.arkesel.com/api/v2/sms/send/contact-group',
            CURLOPT_HTTPHEADER => ['api-key: ' . $this->apiKey],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query([
                'sender' => $sender,
                'message' => $message,
                'group_name' => $groupName
            ]),
        ]);
        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);
        if ($error) {
            throw new \Exception('Arkesel Group SMS Error: ' . $error);
        }
        return $response;
    }

        /**
     * Send Voice SMS
     */
    public function sendVoiceSMS($recipients, $voiceId, $voiceFilePath, $options = [])
    {
        $postFields = [
            'recipients' => $recipients,
            'voice_id' => $voiceId,
            'voice_file' => new \CURLFile($voiceFilePath),
        ];
        if (!empty($options['retry'])) {
            $postFields['retry'] = $options['retry'];
        }
        if (!empty($options['callback_url'])) {
            $postFields['callback_url'] = $options['callback_url'];
        }
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://sms.arkesel.com/api/v2/sms/voice/send',
            CURLOPT_HTTPHEADER => ['api-key: ' . $this->apiKey],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $postFields,
        ]);
        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);
        if ($error) {
            throw new \Exception('Arkesel Voice SMS Error: ' . $error);
        }
        return $response;
    }

    /**
     * Generate OTP
     */
    public function generateOTP($fields)
    {
        $postvars = '';
        foreach ($fields as $key => $value) {
            $postvars .= $key . '=' . $value . '&';
        }
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://sms.arkesel.com/api/otp/generate',
            CURLOPT_HTTPHEADER => ['api-key: ' . $this->apiKey],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $postvars,
        ]);
        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);
        if ($error) {
            throw new \Exception('Arkesel OTP Generate Error: ' . $error);
        }
        return $response;
    }

    /**
     * Verify OTP
     */
    public function verifyOTP($fields)
    {
        $postvars = '';
        foreach ($fields as $key => $value) {
            $postvars .= $key . '=' . $value . '&';
        }
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://sms.arkesel.com/api/otp/verify',
            CURLOPT_HTTPHEADER => ['api-key: ' . $this->apiKey],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $postvars,
        ]);
        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);
        if ($error) {
            throw new \Exception('Arkesel OTP Verify Error: ' . $error);
        }
        return $response;
    }
}
