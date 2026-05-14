<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

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
                'message' => "Sorry, i don't understand. Please use one of the following keyword :

Hello
Audio
Video
Image
File",
            ],
        };

        if (is_string($sender) && $sender !== '') {
            $this->sendFonnte($sender, $reply);
        }

        return response()->json(['ok' => true]);
    }

    private function sendFonnte(string $target, array $data): string
    {
        $token = config('services.fonnte.token');

        $response = Http::withHeaders([
            'Authorization' => $token,
        ])->asForm()->post('https://api.fonnte.com/send', [
            'target' => $target,
            'message' => $data['message'] ?? '',
            'url' => $data['url'] ?? '',
            'filename' => $data['filename'] ?? '',
        ]);

        return $response->body();
    }
}
