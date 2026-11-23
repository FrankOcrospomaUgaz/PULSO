<?php

use Carbon\Carbon;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('backup:run', function () {
    $backups = Storage::disk('backups');
    $filePath = $backups->path(env('BACKUP_FILE_NAME'));

    $date = Carbon::now()->format('Y-m-d');
    $fileName = 'hotelhub_' . $date . '.gz';
    $bucketName = env('GOOGLE_CLOUD_STORAGE_BUCKET_BACKUPS');

    $previousDate = Carbon::now()->subDays(3)->format('Y-m-d');
    $previousFileName = 'hotelhub_' . $previousDate . '.gz';

    $storage = new StorageClient([
        'projectId' => env('GOOGLE_CLOUD_PROJECT_ID'),
        'keyFilePath' => storage_path(env('GOOGLE_CLOUD_KEY_FILE')),
    ]);

    $bucket = $storage->bucket($bucketName);

    $object = $bucket->object($previousFileName);
    if ($object->exists()) $object->delete();

    $subject = 'HotelHUB';
    $emails = explode('|', env('BACKUP_EMAILS'));
    $status = '🟢 SUCCESS';
    $info = array(
        'size' => 0,
        'file_name' => $fileName,
        'info' => 'Backup successfully uploaded',
    );

    $file = null;
    $object = null;

    try {
        $file = fopen($filePath, 'r');
        $info['size'] = filesize($filePath);

        $object = $bucket->upload($file, [
            'name' => $fileName,
        ]);
    } catch (\Throwable $exception) {
        $status = '🔴 ERROR';
        $info['info'] = $exception->getMessage();
    }

    $subject = "[$status] $subject - Backup status";
    $body = '<ul>';
    $body .= '<li>File name: ' . $info['file_name'] . '</li>';
    $body .= '<li>File size: ' . $info['size'] . ' bytes</li>';
    $body .= '<li>Info: ' . $info['info'] . '</li>';
    $body .= '</ul>';

    $fields = [
        'token' => config('services.comprobantes_mail.token'),
        "to" => $emails,
        "bcc" => [],
        "cc" => [],
        "subject" => $subject,
        "body" => $body,
        "attachments" => [],
    ];

    // Enviar correo
    $client = new \GuzzleHttp\Client();
    $response = $client->post("https://facturae-garzasoft.com/facturacion/enviaEmail/envia_correo_json.php", [
        'json' => $fields,
    ]);

    if ($response->getStatusCode() != 200) {
        throw new \Exception('Error al enviar el correo de backup.');
    }

    $data = json_decode($response->getBody());
    if ($data->code != 0) {
        throw new \Exception('Error al enviar correo: código ' . $data->code);
    }
})->purpose('Backup the entire database');
