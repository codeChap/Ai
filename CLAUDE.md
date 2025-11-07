# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

A PHP library providing a unified interface for multiple AI services (OpenAI, Anthropic, Mistral, Groq, xAI, Google). The library standardizes interactions across providers while maintaining service-specific features.

## Development Commands

### Testing
```bash
# Run all tests
composer test

# Run tests with coverage report
composer test:coverage

# Run tests using shell script
./run-tests.sh
```

### Manual Testing
- `run.php` - Contains examples for testing each service
- `judge.php` - Additional testing script

## Architecture

### Request Flow

```
User Code → Ai (factory) → Service → Curl → AI Provider API
                ↓              ↓        ↓
          Auto-discover   Format     HTTP
          from /Services  Messages   Request
```

### Core Components

**Main Entry Point (`src/Ai.php`)**
- Factory class that instantiates the appropriate service based on service type
- Automatic service discovery: scans `src/Services/` directory for `*Service.php` files
- Service registry pattern for extensibility
- **Important**: Service types are case-insensitive and automatically converted to lowercase
- Acts as a proxy: `set()`, `get()`, and `query()` calls are forwarded to the underlying service

**Service Base (`src/Abstracts/AbstractAiService.php`)**
- All service implementations extend this abstract class
- Defines common API configuration properties: `apiKey`, `baseUrl`, `systemPrompt`
- Model configuration: `model`, `temperature`, `maxTokens`, `stop`, `stream`
- Two response methods: `one()` returns first response, `all()` returns array of all responses
- Template methods: `query()`, `extractFirstResponse()`, `extractAllResponses()`

**HTTP Layer (`src/Curl.php`)**
- Handles all HTTP requests to AI service APIs
- Supports both standard and streaming responses
- Streaming: uses `CURLOPT_WRITEFUNCTION` callback to handle chunks and prints directly to output
- Response handling: automatically decodes JSON and throws `ResponseException` on errors
- Always sets `Content-Type: application/json` unless already present

**Traits**
- `AiServiceTrait`: Message formatting logic for converting prompts to API message arrays
- `HeadersTrait`: HTTP header management and formatting
- `PropertyAccessTrait`: Generic getter/setter methods using `property_exists()` checks

### Service Implementations

Each service in `src/Services/` follows this pattern:
1. Extends `AbstractAiService`
2. Uses `AiServiceTrait`, `HeadersTrait`, `PropertyAccessTrait`
3. Defines service-specific properties (model parameters, API options)
4. Implements `query()` method: validates input, formats messages, builds request payload
5. Overrides `extractFirstResponse()`/`extractAllResponses()` if custom logic needed

**Service-Specific Features:**
- **XaiService**: Supports tool calls, citations, live search parameters, search_parameters
- **OpenAiService**: Native JSON mode via `response_format`, reasoning_effort parameter
- **GroqService**: Vision support
- **AnthropicService/MistralService/GoogleService**: Standard chat completions

### JSON Handling (`src/Helpers/JsonExtractor.php`)

Non-OpenAI services use post-processing for JSON responses:
- Extracts outermost JSON structure from text (handles markdown code blocks)
- Uses stack-based bracket matching algorithm
- Handles escaped characters and string boundaries correctly
- Services set `$this->json = true` to enable automatic extraction

### Service Discovery

When instantiating `new Ai('servicetype', $apiKey)`:
1. Checks service registry cache
2. If empty, scans `src/Services/` for files matching `*Service.php`
3. Registers each service with lowercase name (e.g., `XaiService` → `xai`)
4. Validates service exists, instantiates service class
5. Returns `Ai` instance with service injected

## Adding New AI Services

To add a new service:

1. **Create the service file**: `src/Services/YourServiceNameService.php`

2. **Boilerplate setup**:
   ```php
   <?php
   namespace codechap\ai\Services;
   
   use codechap\ai\Abstracts\AbstractAiService;
   use codechap\ai\Traits\AiServiceTrait;
   use codechap\ai\Traits\PropertyAccessTrait;
   use codechap\ai\Traits\HeadersTrait;
   use codechap\ai\Curl;
   
   class YourServiceNameService extends AbstractAiService {
       use AiServiceTrait;
       use HeadersTrait;
       use PropertyAccessTrait;
       
       protected string $model = 'default-model-name';
       // Add other service-specific properties...
       protected $curl;
   ```

3. **Constructor**: Set the API base URL
   ```php
   public function __construct(string $apiKey, string $url = 'https://api.provider.com/v1/') {
       parent::__construct($apiKey, $url);
   }
   ```

4. **Implement `query()` method**:
   ```php
   public function query(string|array $prompts): self {
       $this->validatePrompts($prompts);
       $messages = $this->formatMessages($prompts, $this->systemPrompt);
       
       // Build request data - use array_filter to remove nulls
       $data = array_filter([
           'messages' => $messages,
           'model' => $this->model,
           'temperature' => $this->temperature,
           // ... other parameters
       ], fn($value) => !is_null($value));
       
       $headers = $this->getHeaders([
           'Authorization' => "Bearer " . trim($this->apiKey)
       ]);
       
       $this->curl = new Curl();
       $this->curl->post($data, $headers, $this->baseUrl . 'chat/completions');
       return $this;
   }
   ```

5. **Override response extraction if needed**: Most services can use default implementation. Override only if:
   - API returns different response structure than `choices[0].message.content`
   - Need to handle tool calls, citations, or other special features
   - Need custom JSON extraction logic

6. **Stub the models() method**:
   ```php
   public function models($column = false): array {
       return []; // @todo Implement
   }
   ```

7. **Auto-discovery**: Service is automatically discovered on next `new Ai('yourservicename', $key)` call

## Key Implementation Details

**Message Format Handling:**
- Single string prompt → converted to `[['role' => 'user', 'content' => $prompt]]`
- Array of messages → validated for required `role` and `content` keys
- System prompt added as first message if provided
- **Bug in `AiServiceTrait.php:41`**: When prompt is array, it returns `$prompt` instead of `$messages`, ignoring system prompt

**Property Access Pattern:**
```php
$ai->set('temperature', 0)
   ->set('model', 'grok-4')
   ->set('json', true)
   ->query("Your prompt")
   ->one();
```

The `set()` method uses `PropertyAccessTrait` which throws an `Exception` if property doesn't exist. All properties must be defined in the service class.

**Response Extraction:**
- Default path: `$response['choices'][0]['message']['content']`
- XaiService handles tool_calls and citations differently
- JSON mode (non-OpenAI): extracts and validates JSON from text response
- GroqService has additional fallback for markdown code blocks in JSON extraction

**Error Handling:**
- `InvalidArgumentException`: Empty/invalid service type or API key (thrown in constructors)
- `ResponseException`: HTTP errors or cURL failures (from `Curl.php`)
- `RuntimeException`: JSON extraction/encoding failures (from services with JSON mode)
- `Exception`: Property access errors from `PropertyAccessTrait` (generic Exception, not typed)

## Testing Structure

Tests in `tests/` directory:
- One test file per service (e.g., `OpenAiServiceTest.php`)
- Tests service instantiation and basic functionality
- Uses PHPUnit with colors enabled and warning details
- Coverage reports generated to `coverage/` directory

## Common Pitfalls & Gotchas

1. **Service Type Case Sensitivity**: Service types are auto-converted to lowercase with E_USER_WARNING. Always use lowercase to avoid warnings.

2. **Property Setting**: Using `set()` with a non-existent property throws generic `Exception`. Check service class properties before setting.

3. **JSON Mode Differences**: 
   - OpenAI: Native support via `response_format` parameter
   - Others: Post-processing with `JsonExtractor` (may fail if AI doesn't return valid JSON)

4. **System Prompt Bug**: Array-based prompts ignore system prompt due to bug at `AiServiceTrait.php:41`

5. **Streaming Side Effect**: Streaming prints output directly to stdout during request execution, not just on retrieval.

6. **Response Type Variations**: 
   - Most services return `string` from `one()`
   - XaiService can return `array` for tool calls or citations
   - Check return type when working with XaiService

7. **Vision Message Format**: Vision requires nested array structure:
   ```php
   [[
       'role' => 'user',
       'content' => [
           ['type' => 'image_url', 'image_url' => ['url' => $url]],
           ['type' => 'text', 'text' => $prompt]
       ]
   ]]
   ```

## Important Notes

- PHP 8.2+ required (uses typed properties, array_filter with callbacks)
- Service registry is static and persists across instances
- No streaming support implemented yet (property exists but handling incomplete)
- `models()` method returns empty array on all services (marked as @todo)
