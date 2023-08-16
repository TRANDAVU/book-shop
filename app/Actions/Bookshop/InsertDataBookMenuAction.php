<?php

namespace App\Actions\Bookshop;
use Google\Client;
use Google\Service\Sheets;
use Illuminate\Support\Facades\DB;
class InsertDataBookMenuAction
{

    public static function getData($sheet)
    {
        $client = new Client();
        $client->setApplicationName('Your App Name');
        $client->setScopes([Sheets::SPREADSHEETS_READONLY]);
        $client->setAuthConfig(storage_path('credentials.json'));
        $client->setAccessType('offline'); // Thay đổi dựa trên nhu cầu của bạn

        $service = new Sheets($client);

        $spreadsheetId = '1ezq5ThxFIliPKe1ib7nuldoLtJOe0VHtGF2BKgjw2b4';
        $range = $sheet.'!A:H';
        // Điều chỉnh phạm vi theo nhu cầu

        $response = $service->spreadsheets_values->get($spreadsheetId, $range);

        $values = $response->getValues();

        return $values;
    }

    public static function Insert($sheet,$values,$ID_product,$table){
        $client = new Client();
        $client->setApplicationName('Your App Name');
        $client->setScopes([Sheets::SPREADSHEETS]);
        $client->setAuthConfig(storage_path('credentials.json'));
        $client->setAccessType('offline'); // Change this based on your use case

        $service = new Sheets($client);

        $spreadsheetId = '1ezq5ThxFIliPKe1ib7nuldoLtJOe0VHtGF2BKgjw2b4';
        $range = $sheet.'!A2'; // Adjust this range as needed bookshops

        $body = new \Google_Service_Sheets_ValueRange([
            'values' => $values,
        ]);

        $params = [
            'valueInputOption' => 'RAW',
        ];

        $result = $service->spreadsheets_values->append($spreadsheetId, $range, $body, $params);
        $updatedRange = $result->tableRange;

        DB::table($table)->where('id',$ID_product)->update(['range'=>$updatedRange]);

    }

    public static function Update($sheet,$values,$ID_product,$table){
        $client = new Client();
        $client->setApplicationName('Your App Name');
        $client->setScopes([Sheets::SPREADSHEETS]);
        $client->setAuthConfig(storage_path('credentials.json'));
        $client->setAccessType('offline'); // Change this based on your use case

        $service = new Sheets($client);

        $spreadsheetId = '1ezq5ThxFIliPKe1ib7nuldoLtJOe0VHtGF2BKgjw2b4';
        $r=DB::table($table)->where('id',$ID_product)->pluck('range')->first();
        $range = $sheet.'!A'.self::calculateValueFromCellRange($r); // Adjust this range as needed bookshops

        $body = new \Google_Service_Sheets_ValueRange([
            'values' => $values,
        ]);

        $params = [
            'valueInputOption' => 'RAW',
        ];

        $result = $service->spreadsheets_values->update($spreadsheetId, $range, $body, $params);
    }


    private static function calculateValueFromCellRange($range) {
        // Tách chuỗi để lấy phần số ở cuối
        $parts = explode(':', $range);
        $lastCell = end($parts);
        preg_match('/\d+$/', $lastCell, $matches);

        // Lấy số ở cuối và cộng thêm 1
        $number = intval($matches[0]);
        $result = $number + 1;

        return $result;
    }

}
