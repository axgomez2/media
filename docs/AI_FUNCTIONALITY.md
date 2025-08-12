# AI Content Generation - News System

## Overview

The news system includes AI-powered content generation functionality that helps administrators create content more efficiently. The system supports generating titles, excerpts, full content, keywords, meta descriptions, and meta keywords using OpenAI's API.

## Features

### Supported Content Types

1. **Title** - Generates SEO-optimized titles (50-60 characters)
2. **Excerpt** - Creates engaging summaries (150-160 characters)
3. **Content** - Produces full articles with proper structure
4. **Keywords** - Generates SEO keywords (comma-separated)
5. **Meta Description** - Creates meta descriptions for SEO (max 160 characters)
6. **Meta Keywords** - Generates specific meta keywords

### User Interface

- **AI Buttons**: Purple "IA" buttons appear next to relevant form fields
- **Modal Interface**: Clean, accessible modal for content generation
- **Loading States**: Visual feedback during content generation
- **Toast Notifications**: Success/error messages with auto-dismiss
- **Automatic Insertion**: Generated content is automatically inserted into form fields

### Technical Features

- **Fallback Content**: Works even when AI API is unavailable
- **Rate Limiting**: Prevents API abuse (100 requests/hour)
- **Error Handling**: Graceful degradation with helpful error messages
- **Content Processing**: Automatic formatting and character limits
- **Accessibility**: Keyboard navigation and screen reader support

## Configuration

### Environment Variables

Add to your `.env` file:

```env
OPENAI_API_KEY=your_openai_api_key_here
OPENAI_MODEL=gpt-3.5-turbo
OPENAI_MAX_TOKENS=1000
OPENAI_TEMPERATURE=0.7
```

### Service Configuration

The AI service is configured in `config/services.php`:

```php
'openai' => [
    'api_key' => env('OPENAI_API_KEY'),
    'model' => env('OPENAI_MODEL', 'gpt-3.5-turbo'),
    'max_tokens' => env('OPENAI_MAX_TOKENS', 1000),
    'temperature' => env('OPENAI_TEMPERATURE', 0.7),
],
```

## Usage

### For Administrators

1. **Creating/Editing News**:
   - Navigate to the news create/edit form
   - Click the purple "IA" button next to any supported field
   - Enter a descriptive prompt in the modal
   - Select the content type (auto-selected based on field)
   - Click "Gerar" to generate content
   - Review and edit the generated content as needed

2. **Best Practices for Prompts**:
   - Be specific about the topic
   - Include target audience information
   - Mention key points to cover
   - Specify tone or style preferences

### Example Prompts

- **Title**: "Artigo sobre benefícios da energia solar para pequenas empresas"
- **Content**: "Artigo completo sobre como a inteligência artificial está transformando o marketing digital, incluindo exemplos práticos"
- **Keywords**: "Palavras-chave para artigo sobre sustentabilidade empresarial"

## API Endpoints

### Generate Content

**POST** `/admin/news/generate-content`

**Parameters:**
- `prompt` (required): Description of content to generate
- `type` (required): One of: title, excerpt, content, keywords, meta_description, meta_keywords
- `context` (optional): Additional context for generation

**Response:**
```json
{
    "success": true,
    "content": "Generated content here",
    "type": "title",
    "rate_limit": {
        "current": 5,
        "limit": 100,
        "remaining": 95
    }
}
```

## File Structure

```
app/
├── Services/
│   └── AIContentService.php          # Core AI service
├── Http/Controllers/Admin/
│   └── NewsController.php            # generateContent method
resources/
├── js/admin/
│   └── ai-content.js                 # Frontend JavaScript
└── views/admin/news/
    ├── create.blade.php              # Create form with AI buttons
    └── edit.blade.php                # Edit form with AI buttons
tests/
├── Unit/Services/
│   └── AIContentServiceTest.php     # Service unit tests
└── Feature/Admin/
    └── AIContentGenerationTest.php  # API endpoint tests
```

## Error Handling

### Common Scenarios

1. **API Unavailable**: Returns fallback content with success=true
2. **Rate Limit Exceeded**: Returns 429 status with retry information
3. **Invalid Input**: Returns 422 validation errors
4. **API Errors**: Returns fallback content with error logging
5. **Network Timeout**: Graceful fallback with user notification

### Fallback Content

When AI is unavailable, the system provides template content:

- **Title**: "Título gerado automaticamente - Edite conforme necessário"
- **Excerpt**: "Resumo gerado automaticamente - Edite este texto..."
- **Content**: Structured template with sections
- **Keywords**: Basic keyword suggestions

## Security

### Input Validation

- Prompt length: 10-500 characters
- Character filtering: Prevents injection attacks
- Type validation: Only allowed content types
- Rate limiting: Prevents API abuse

### Authentication

- Requires admin authentication
- CSRF protection on all requests
- Proper authorization middleware

## Performance

### Caching

- Rate limit status cached for 1 hour
- No content caching (ensures fresh generation)

### Optimization

- Async JavaScript for non-blocking UI
- Debounced user interactions
- Efficient DOM manipulation
- Minimal API calls

## Troubleshooting

### Common Issues

1. **AI buttons not working**:
   - Check if JavaScript file is loaded
   - Verify CSRF token is present
   - Check browser console for errors

2. **API errors**:
   - Verify OpenAI API key is valid
   - Check rate limits
   - Review server logs

3. **Content not inserting**:
   - Ensure field IDs match
   - Check for JavaScript errors
   - Verify modal functionality

### Debug Mode

Enable debug logging by setting `LOG_LEVEL=debug` in `.env` to see detailed AI service logs.

## Testing

Run the test suite:

```bash
# Unit tests
php artisan test tests/Unit/Services/AIContentServiceTest.php

# Feature tests
php artisan test tests/Feature/Admin/AIContentGenerationTest.php

# All AI-related tests
php artisan test --filter AI
```

## Future Enhancements

- Image generation for featured images
- Content translation
- SEO score analysis
- Content optimization suggestions
- Bulk content generation
- Custom prompt templates
