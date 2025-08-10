<?php

namespace App\Http\Requests;

use App\Rules\NewsImageValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNewsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $news = $this->route('news');
        $newsId = $news ? ($news->id ?? $news) : null;

        return [
            'title' => [
                'required',
                'string',
                'max:255',
                'min:3'
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('news', 'slug')->ignore($newsId)
            ],
            'excerpt' => [
                'nullable',
                'string',
                'max:500'
            ],
            'content' => [
                'required',
                'string',
                'min:10'
            ],
            'featured_image' => [
                'nullable',
                NewsImageValidation::featured()
            ],
            'remove_featured_image' => [
                'boolean'
            ],
            'gallery_images' => [
                'nullable',
                'array',
                'max:10'
            ],
            'gallery_images.*' => [
                NewsImageValidation::gallery()
            ],
            'remove_gallery_images' => [
                'nullable',
                'array'
            ],
            'remove_gallery_images.*' => [
                'string'
            ],
            'topics' => [
                'nullable',
                'array',
                'max:5'
            ],
            'topics.*' => [
                'integer',
                'exists:news_topics,id'
            ],
            'status' => [
                'required',
                Rule::in(['draft', 'published', 'archived'])
            ],
            'meta_description' => [
                'nullable',
                'string',
                'max:160'
            ],
            'meta_keywords' => [
                'nullable',
                'string',
                'max:255'
            ],
            'published_at' => [
                'nullable',
                'date'
            ],
            'featured' => [
                'boolean'
            ]
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'O título é obrigatório.',
            'title.min' => 'O título deve ter pelo menos 3 caracteres.',
            'title.max' => 'O título não pode exceder 255 caracteres.',
            'slug.regex' => 'O slug deve conter apenas letras minúsculas, números e hífens.',
            'slug.unique' => 'Este slug já está sendo usado por outra notícia.',
            'excerpt.max' => 'O resumo não pode exceder 500 caracteres.',
            'content.required' => 'O conteúdo é obrigatório.',
            'content.min' => 'O conteúdo deve ter pelo menos 10 caracteres.',
            'featured_image.image' => 'A imagem de destaque deve ser um arquivo de imagem válido.',
            'featured_image.mimes' => 'A imagem de destaque deve ser do tipo: jpeg, jpg, png ou webp.',
            'featured_image.max' => 'A imagem de destaque não pode exceder 2MB.',
            'featured_image.dimensions' => 'A imagem de destaque deve ter pelo menos 300x200 pixels.',
            'gallery_images.max' => 'Você pode adicionar no máximo 10 imagens à galeria.',
            'gallery_images.*.image' => 'Todos os arquivos da galeria devem ser imagens válidas.',
            'gallery_images.*.mimes' => 'As imagens da galeria devem ser do tipo: jpeg, jpg, png ou webp.',
            'gallery_images.*.max' => 'Cada imagem da galeria não pode exceder 2MB.',
            'gallery_images.*.dimensions' => 'As imagens da galeria devem ter pelo menos 200x150 pixels.',
            'topics.max' => 'Você pode selecionar no máximo 5 tópicos.',
            'topics.*.exists' => 'Um ou mais tópicos selecionados não existem.',
            'status.required' => 'O status é obrigatório.',
            'status.in' => 'O status deve ser: rascunho, publicado ou arquivado.',
            'meta_description.max' => 'A meta descrição não pode exceder 160 caracteres.',
            'meta_keywords.max' => 'As palavras-chave não podem exceder 255 caracteres.',
            'published_at.date' => 'A data de publicação deve ser uma data válida.'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'title' => 'título',
            'slug' => 'slug',
            'excerpt' => 'resumo',
            'content' => 'conteúdo',
            'featured_image' => 'imagem de destaque',
            'gallery_images' => 'galeria de imagens',
            'topics' => 'tópicos',
            'status' => 'status',
            'meta_description' => 'meta descrição',
            'meta_keywords' => 'palavras-chave',
            'published_at' => 'data de publicação',
            'featured' => 'destaque'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert featured checkbox to boolean
        if ($this->has('featured')) {
            $this->merge([
                'featured' => $this->boolean('featured')
            ]);
        }

        // Convert remove_featured_image checkbox to boolean
        if ($this->has('remove_featured_image')) {
            $this->merge([
                'remove_featured_image' => $this->boolean('remove_featured_image')
            ]);
        }

        // If status is being changed to published and no published_at is set, set it to now
        if ($this->status === 'published' && !$this->published_at) {
            $news = $this->route('news');
            if ($news && (!$news->published_at || $news->status !== 'published')) {
                $this->merge([
                    'published_at' => now()
                ]);
            }
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Custom validation: if removing featured image, ensure no new featured image is being uploaded
            if ($this->boolean('remove_featured_image') && $this->hasFile('featured_image')) {
                $validator->errors()->add('featured_image', 'Não é possível remover e adicionar uma imagem de destaque ao mesmo tempo.');
            }

            // Custom validation: ensure published articles have required SEO fields
            if ($this->input('status') === 'published') {
                if (empty($this->input('meta_description'))) {
                    $validator->errors()->add('meta_description', 'A meta descrição é obrigatória para artigos publicados.');
                }
            }
        });
    }
}
