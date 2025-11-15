<?php

namespace App\Livewire\CvScreening;

use App\Jobs\ProcessCv;
use App\Models\CurriculumVitae;
use App\Models\AiModel;
use App\Enums\ModelStatus;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class Form extends Component
{
    use WithFileUploads;

    public $model_id = 'gpt-4o-mini';
    public $job_offer;
    public $file;

    /**
     * Collection of active models for the select input.
     */
    public $models = [];

    protected $rules = [
        'model_id' => 'nullable|string|exists:models,id',
        'job_offer' => 'required|string',
        'file' => 'required|file|mimes:pdf|max:5120',
    ];

    public function submit(): void
    {
        $this->validate();

        $path = $this->file->store('cv', 's3');
        $url = Storage::disk('s3')->url($path);

        $cv = CurriculumVitae::create([
            'model_id' => $this->model_id,
            'job_offer' => $this->job_offer,
            'file_name' => $this->file->getClientOriginalName(),
            'file_size' => $this->file->getSize(),
            'file_url' => $url,
            'summary' => null,
            'cover_letter' => null,
            'user_id' => auth()->id(),
        ]);

        // Dispatch background job to process the document
        ProcessCv::dispatch($cv->id);

        // Close modal first to avoid re-render hiding the modal before it closes
        $this->modal('cv-form-modal')->close();

        // Reset local state
        $this->reset(['job_offer', 'file']);

        // Notify parent/listeners that a CV was created
        $this->dispatch('cv-created');
    }

    public function mount(): void
    {
        // Load active models for the select input
        $this->models = AiModel::query()
            ->where('status', ModelStatus::Active->value)
            ->orderBy('owned_by')
            ->get(['id', 'object', 'owned_by']);
    }

    public function render()
    {
        return view('livewire.cv-screening.form');
    }
}
