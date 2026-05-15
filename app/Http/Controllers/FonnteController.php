<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use GuzzleHttp\Client;

class FonnteController extends Controller
{
    /**
     * Fonnte incoming webhook (JSON body).
     */
    public function webhook(Request $request)
    {
        $data = $request->only([
            'device',
            'sender',
            'message',
            'member',
            'name',
            'location',
            'url',
            'filename',
            'extension',
        ]);

        $message = Str::lower(trim((string) ($data['message'] ?? '')));
        $sender = $data['sender'] ?? null;

        $reply = match ($message) {
            'test' => [
                'message' => 'working great!',
            ],
            'image' => [
                'message' => 'image message',
                'url' => 'https://filesamples.com/samples/image/jpg/sample_640%C3%97426.jpg',
            ],
            'audio' => [
                'message' => 'audio message',
                'url' => 'https://filesamples.com/samples/audio/mp3/sample3.mp3',
                'filename' => 'music',
            ],
            'video' => [
                'message' => 'video message',
                'url' => 'https://filesamples.com/samples/video/mp4/sample_640x360.mp4',
            ],
            'file' => [
                'message' => 'file message',
                'url' => 'https://filesamples.com/samples/document/docx/sample3.docx',
                'filename' => 'document',
            ],
            default => [
                'message' => "Please wait...",
            ],
        };

        if (is_string($sender) && $sender !== '') {
            $this->sendFonnte($sender, $data, $reply);
        }

        return response()->json(['ok' => true]);
    }

    private function sendFonnte(string $target, array $data, array $reply): string
    {
        try {
            $token = config('services.fonnte.token');

            $response = Http::withHeaders([
                'Authorization' => $token,
            ])->asForm()->post('https://api.fonnte.com/send', [
                        // 'target' => $target,
                        'target' => '120363339779974202@g.us',
                        'message' => $reply['message'] ?? '',
                        'url' => $reply['url'] ?? '',
                        'filename' => $reply['filename'] ?? '',
                    ]);

            // $client = new Client();
            // $client->post(config('services.n8n.webhook_url'), [
            //     'json' => [
            //         // 'sender' => $target,
            //         'sender' => '120363339779974202@g.us',
            //         'message' => $data['message'] ?? '',
            //     ],
            //     'timeout' => 10,
            //     'verify' => false,
            // ]);

            return $response->body();
        } catch (\Exception $e) {
            \Log::error('Webhook Error: ' . $e->getMessage());
            throw $e;
        }
    }
}

