<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ClientSearchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'search' => [
                'nullable',
                'string',
                'max:255',
                'min:2',
                'regex:/^[a-zA-Z0-9\s@._-]+$/' // Permitir apenas caracteres seguros
            ],
            'verified' => [
                'nullable',
                'in:all,verified,unverified'
            ],
            'account_status' => [
                'nullable',
                'in:all,active,inactive'
            ],
            'period' => [
                'nullable',
                'in:all,today,week,month,year,custom'
            ],
            'start_date' => [
                'nullable',
                'date',
                'before_or_equal:today',
                'required_if:period,custom'
            ],
            'end_date' => [
                'nullable',
                'date',
                'after_or_equal:start_date',
                'before_or_equal:today',
                'required_if:period,custom'
            ],
            'per_page' => [
                'nullable',
                'integer',
                'min:10',
                'max:100'
            ],
            'sort_by' => [
                'nullable',
                'in:name,email,created_at,updated_at,orders_count'
            ],
            'sort_direction' => [
                'nullable',
                'in:asc,desc'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'search.min' => 'A busca deve ter pelo menos 2 caracteres.',
            'search.max' => 'A busca não pode ter mais de 255 caracteres.',
            'search.regex' => 'A busca contém caracteres inválidos.',
            'verified.in' => 'Status de verificação inválido.',
            'account_status.in' => 'Status da conta inválido.',
            'period.in' => 'Período selecionado inválido.',
            'start_date.date' => 'Data de início deve ser uma data válida.',
            'start_date.before_or_equal' => 'Data de início não pode ser futura.',
            'start_date.required_if' => 'Data de início é obrigatória para período personalizado.',
            'end_date.date' => 'Data de fim deve ser uma data válida.',
            'end_date.after_or_equal' => 'Data de fim deve ser posterior à data de início.',
            'end_date.before_or_equal' => 'Data de fim não pode ser futura.',
            'end_date.required_if' => 'Data de fim é obrigatória para período personalizado.',
            'per_page.integer' => 'Número de itens por página deve ser um número inteiro.',
            'per_page.min' => 'Mínimo de 10 itens por página.',
            'per_page.max' => 'Máximo de 100 itens por página.',
            'sort_by.in' => 'Campo de ordenação inválido.',
            'sort_direction.in' => 'Direção de ordenação inválida.'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'search' => 'busca',
            'verified' => 'status de verificação',
            'account_status' => 'status da conta',
            'period' => 'período',
            'start_date' => 'data de início',
            'end_date' => 'data de fim',
            'per_page' => 'itens por página',
            'sort_by' => 'ordenar por',
            'sort_direction' => 'direção da ordenação'
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        \Log::warning('Validação falhou na busca de clientes', [
            'errors' => $validator->errors()->toArray(),
            'input' => $this->except(['_token']),
            'user_id' => auth()->id(),
            'ip' => $this->ip(),
            'user_agent' => $this->userAgent()
        ]);

        parent::failedValidation($validator);
    }
}
