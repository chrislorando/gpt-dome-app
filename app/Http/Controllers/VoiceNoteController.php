<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use OpenAI\Laravel\Facades\OpenAI;

class VoiceNoteController extends Controller
{
    public function transcribeChunk(Request $request)
    {
        try {
            $request->validate([
                'audio_chunk' => 'required|file',
            ]);

            $audioChunk = $request->file('audio_chunk');

            $filename = 'chunk_'.now()->timestamp.'_'.substr(str()->uuid(), 0, 8).'.webm';
            Storage::disk('local')->putFileAs('temp', $audioChunk, $filename);
            $fullPath = Storage::disk('local')->path('temp/'.$filename);

            $response = OpenAI::audio()->transcribe([
                'model' => 'whisper-1',
                'file' => fopen($fullPath, 'r'),
                'response_format' => 'json',
            ]);

            Storage::disk('local')->delete('temp/'.$filename);

            return response()->json([
                'text' => $response->text,
            ]);
        } catch (\Exception $e) {
            \Log::error('Transcription failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Transcription failed: '.$e->getMessage(),
            ], 500);
        }
    }
}
