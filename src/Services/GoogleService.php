<?php

namespace codechap\ai\Services;

use codechap\ai\Abstracts\AbstractAiService;
use codechap\ai\Traits\AiServiceTrait;
use codechap\ai\Traits\PropertyAccessTrait;
use codechap\ai\Traits\HeadersTrait;
use codechap\ai\Curl;
use codechap\ai\Helpers\JsonExtractor;
use RuntimeException;
use JsonException;

/**
 * Service class for interacting with the Google Generative Language API (e.g., Gemini).
 */
class GoogleService extends AbstractAiService
{
    use AiServiceTrait; // Provides common AI service functionalities like message formatting
    use HeadersTrait; // Manages HTTP headers
    use PropertyAccessTrait; // Allows setting protected properties via magic __set

    // --- Configuration ---
    protected string $apiKey; // Google AI API Key
    protected string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/'; // Base URL for the API

    // --- Model & Generation Parameters ---
    protected string $systemPrompt     = 'You are a helpful assistant.'; // Default system prompt (handled within message formatting)
    protected string $model            = 'gemini-2.5-pro-preview-03-25'; // Default model to use
    protected ?float $temperature      = null; // Controls randomness (0.0-1.0)
    protected ?float $topP             = null; // Nucleus sampling threshold
    protected ?int $topK               = null; // Top-k sampling parameter
    protected ?int $maxOutputTokens    = null; // Maximum number of tokens to generate
    protected ?array $stopSequences    = null; // Sequences where the API will stop generating
    protected ?bool $json              = false; // Flag to indicate if JSON output is expected

    // --- Safety Settings (Optional - Not implemented in detail here) ---
    // protected ?array $safetySettings = null;

    // --- Tools / Function Calling (Optional) ---
    protected ?array $tools            = null; // Definitions of tools the model can use
    protected mixed $toolConfig        = null; // Configuration for tool usage (e.g., function calling mode)

    // --- Internal ---
    // Curl instance inherited from AbstractAiService

    /**
     * Constructor.
     *
     * @param string $apiKey Your Google AI API key.
     * @param string $url Optional base URL override.
     */
    public function __construct(string $apiKey, string $url = 'https://generativelanguage.googleapis.com/v1beta/models/')
    {
        parent::__construct($apiKey, $url); // Call parent constructor
    }

    /**
     * Prepare and send the query to the Google AI API.
     *
     * @param string|array $prompts User prompts. Can be a single string or an array for conversation history.
     * @return self The current instance for method chaining.
     * @throws RuntimeException If prompts are invalid.
     */
    public function query(string|array $prompts): self
    {
        $this->validatePrompts($prompts); // Ensure prompts are valid

        // Format messages according to Google's 'contents' structure
        // Note: Google doesn't have an explicit 'system' role like OpenAI.
        // The system prompt is often included as context within the first user message or managed differently.
        // This implementation assumes the system prompt guides the overall interaction context.
        // We'll adapt `formatMessages` or handle the system prompt implicitly/explicitly as needed.
        // For simplicity here, we might prepend system prompt to the first user turn if necessary.
        $contents = $this->formatContents($prompts, $this->systemPrompt);

        // --- Build Generation Configuration ---
        $generationConfig = array_filter([
            'temperature'       => $this->temperature,
            'topP'              => $this->topP,
            'topK'              => $this->topK,
            'maxOutputTokens'   => $this->maxOutputTokens,
            'stopSequences'     => $this->stopSequences,
            // If JSON mode is requested, set the response MIME type
            'responseMimeType'  => $this->json ? 'application/json' : null,
        ], fn($value) => !is_null($value));

        // --- Build Request Body ---
        $data = array_filter([
            'contents'         => $contents,
            'generationConfig' => empty($generationConfig) ? null : $generationConfig,
            'tools'            => $this->tools,
            'toolConfig'       => $this->toolConfig,
            // 'safetySettings' => $this->safetySettings, // Add if implementing safety settings
        ], fn($value) => !is_null($value) && !empty($value));

        // --- Prepare API Request ---
        $headers = $this->getHeaders([
            // Google uses Content-Type: application/json
            'Content-Type' => 'application/json',
            // API key is typically passed as a query parameter
        ]);
        // Construct the specific endpoint URL including the model and action
        $url = rtrim($this->baseUrl, '/') . '/' . $this->model . ':generateContent?key=' . trim($this->apiKey);

        // --- Execute Request ---
        if ($this->curl === null) {
            $this->curl = new Curl();
        }
        $this->curl->post($headers, $url, $data);

        return $this; // Allow method chaining
    }

    /**
     * Formats prompts into Google's 'contents' structure.
     * Handles system prompt by potentially prepending it or relying on model's implicit understanding.
     *
     * @param string|array $prompts The user prompts.
     * @param string $systemPrompt The system prompt.
     * @return array The formatted 'contents' array.
     */
    protected function formatContents(string|array $prompts, string $systemPrompt): array
    {
        $contents = [];
        $isFirstUserPrompt = true;

        if (is_string($prompts)) {
            $prompts = [['role' => 'user', 'content' => $prompts]];
        }

        // Simple approach: Prepend system prompt context to the first user message.
        // More complex scenarios might require structuring differently or using specific API features if available.
        $currentRole = 'user'; // Start with user role typically
        foreach ($prompts as $message) {
            $role = $message['role'] ?? 'user'; // Default to user if role not specified
            $content = $message['content'] ?? '';

            // Google uses 'user' and 'model' roles
            $googleRole = ($role === 'assistant' || $role === 'model') ? 'model' : 'user';

            // If it's the very first user prompt, prepend the system prompt.
            if ($googleRole === 'user' && $isFirstUserPrompt && !empty($systemPrompt)) {
                 // You might choose to format this differently, e.g., add a separate turn.
                 // For simplicity, just prepend. Google models are often good at context.
                $content = $systemPrompt . "\n\n" . $content;
                $isFirstUserPrompt = false;
            }

            $contents[] = [
                'role' => $googleRole,
                'parts' => [['text' => $content]]
            ];
            $currentRole = $googleRole; // Keep track of the last role added
        }

         // Ensure conversation ends with a user message if the last provided was model/assistant
         // Google API often expects the last message in 'contents' to be from the 'user'.
         // However, depending on the use case (like continuing a conversation), this might vary.
         // Let's assume for a standard query, the user asks the last question.
         // If the last message was 'model', we might need to adjust, but typically the input $prompts should end with user turn.


        return $contents;
    }


    /**
     * Get a list of available models from the Google AI API.
     *
     * @param string|null $column Optional: If needed to extract a specific column (currently unused).
     * @return array List of models or empty array on failure/not implemented fully.
     */
    public function models(?string $column = null): array
    {
        // Google API endpoint for listing models
        $url = 'https://generativelanguage.googleapis.com/v1beta/models?key=' . trim($this->apiKey);
        $headers = $this->getHeaders(); // Get default headers (might not need specific ones for GET)

        try {
            if ($this->curl === null) {
                $this->curl = new Curl();
            }
            $this->curl->get($headers, $url); // Perform GET request
            $response = $this->curl->getResponse();

            if (isset($response['models']) && is_array($response['models'])) {
                // Optionally process the list, e.g., extract names
                // For now, return the raw 'models' array part
                return $response['models'];
            }
        } catch (\Exception $e) {
            // Log error or handle appropriately
            // error_log("Failed to fetch Google models: " . $e->getMessage());
            return []; // Return empty on error
        }

        return []; // Return empty if 'models' key isn't found
    }

    /**
     * Get the first response content from the API result.
     *
     * @return array|string The content of the first candidate's response (text or function call).
     * @throws RuntimeException If the response structure is invalid or contains errors/blocks.
     */
    public function one(): array | string
    {
        $response = $this->curl->getResponse();
        return $this->extractFirstResponse($response);
    }

    /**
     * Get all response contents from the API result.
     * Note: Google's standard API usually returns one primary candidate unless configured otherwise.
     * This might return an array with one element in typical cases.
     *
     * @return array An array containing the response content of each candidate.
     * @throws RuntimeException If the response structure is invalid or contains errors/blocks.
     */
    public function all(): array
    {
        $response = $this->curl->getResponse();
        return $this->extractAllResponses($response);
    }

    /**
     * Extract the first response from the Google API result structure.
     * Handles text, JSON extraction, and function calls.
     *
     * @param array $response The decoded JSON response from the API.
     * @return string|array The extracted content (text, decoded JSON, or function call array).
     * @throws RuntimeException For JSON errors, blocked content, or unexpected structure.
     */
    protected function extractFirstResponse(array $response): string|array
    {
        // Check for prompt feedback indicating blocking
        if (isset($response['promptFeedback']['blockReason'])) {
            throw new RuntimeException('Prompt was blocked: ' . ($response['promptFeedback']['blockReason'] ?? 'Unknown reason'));
        }

        // Check if candidates exist
        if (!isset($response['candidates']) || empty($response['candidates'])) {
            // Check for specific error message from Google
             if (isset($response['error']['message'])) {
                 throw new RuntimeException('Google API Error: ' . $response['error']['message']);
             }
            throw new RuntimeException('Invalid response structure: No candidates found.');
        }

        $firstCandidate = $response['candidates'][0];

        // Check for content finish reason (e.g., safety)
        if (isset($firstCandidate['finishReason']) && !in_array($firstCandidate['finishReason'], ['STOP', 'MAX_TOKENS', 'TOOL_CODE', 'OTHER'])) {
             // Note: 'TOOL_CODE' indicates function call expected, which is handled below. 'OTHER' is vague.
            if($firstCandidate['finishReason'] !== 'UNSPECIFIED' && $firstCandidate['finishReason'] !== 'FINISH_REASON_UNSPECIFIED'){ // Check if finish reason indicates an issue
                throw new RuntimeException('Content generation stopped due to: ' . $firstCandidate['finishReason']);
            }
        }


        // Check for content parts
        if (!isset($firstCandidate['content']['parts']) || empty($firstCandidate['content']['parts'])) {
            // It's possible to get a candidate without parts if finishReason is MAX_TOKENS etc. and no content was generated yet.
            // Or if only a function call is present. Let's check for function call specifically.
            if (!isset($firstCandidate['content']['parts'][0]['functionCall'])) {
                 // If no parts and no function call, it's likely an issue or empty response.
                 // Consider the finish reason. If STOP, maybe return empty string?
                 if (($firstCandidate['finishReason'] ?? '') === 'STOP') return '';
                 throw new RuntimeException('Invalid response structure: First candidate has no content parts or function call.');
            }
        }


        $firstPart = $firstCandidate['content']['parts'][0];

        // --- Handle Function Call ---
        if (isset($firstPart['functionCall'])) {
            return $this->handleFunctionCall($firstPart['functionCall']);
        }

        // --- Handle Text Content ---
        if (!isset($firstPart['text'])) {
             // Should have text if not a function call, unless blocked earlier.
             throw new RuntimeException('Invalid response structure: No text or function call found in the first part.');
        }
        $text = $firstPart['text'];


        // --- Handle JSON Extraction (if requested) ---
        if ($this->json) {
            // Google might return JSON directly if responseMimeType was set to application/json
            // Or we might need to extract from markdown code block if not using MIME type.
            // Let's assume JsonExtractor handles finding JSON within the text.
             $extracted = JsonExtractor::extract($text);
            if ($extracted === null) {
                 // If direct JSON MIME type was used, the $text itself should be the JSON string
                 try {
                    // Attempt to decode the raw text as JSON first if MIME type was set
                    return json_decode($text, true, 512, JSON_THROW_ON_ERROR);
                 } catch (JsonException $e) {
                    // Fall through to throwing error if parsing fails
                 }
                throw new RuntimeException('Response does not contain valid JSON, and JSON output was expected.');
            }
            // JsonExtractor returns the decoded JSON (array/object)
             // We typically want to return the JSON *string* from this service level, like other services.
             try {
                 return json_encode($extracted, JSON_THROW_ON_ERROR);
             } catch (JsonException $e) {
                 throw new RuntimeException('Failed to re-encode extracted JSON: ' . $e->getMessage());
             }
        }

        // Return plain text
        return $text;
    }

    /**
     * Extract all responses from the Google API result structure.
     * Usually, Google returns one primary candidate unless requested otherwise.
     *
     * @param array $response The decoded JSON response from the API.
     * @return array An array of extracted contents (text, decoded JSON, or function call array).
     * @throws RuntimeException For JSON errors, blocked content, or unexpected structure in any candidate.
     */
    protected function extractAllResponses(array $response): array
    {
        // Check for prompt feedback indicating blocking (affects all candidates)
        if (isset($response['promptFeedback']['blockReason'])) {
            throw new RuntimeException('Prompt was blocked: ' . ($response['promptFeedback']['blockReason'] ?? 'Unknown reason'));
        }

        if (!isset($response['candidates']) || !is_array($response['candidates'])) {
             // Check for specific error message from Google
             if (isset($response['error']['message'])) {
                 throw new RuntimeException('Google API Error: ' . $response['error']['message']);
             }
            // Allow empty array if no candidates (e.g., maybe valid empty response)
            return [];
            // throw new RuntimeException('Invalid response structure: No candidates array found.');
        }

        $results = [];
        foreach ($response['candidates'] as $index => $candidate) {
            try {
                 // Use a simplified extraction logic similar to extractFirstResponse for each candidate
                 if (isset($candidate['finishReason']) && !in_array($candidate['finishReason'], ['STOP', 'MAX_TOKENS', 'TOOL_CODE', 'OTHER', 'UNSPECIFIED', 'FINISH_REASON_UNSPECIFIED'])) {
                     throw new RuntimeException('Candidate ' . $index . ' stopped due to: ' . $candidate['finishReason']);
                 }

                 if (!isset($candidate['content']['parts']) || empty($candidate['content']['parts'])) {
                      if (!isset($candidate['content']['parts'][0]['functionCall'])) {
                          // If no parts and no function call, and finish reason is STOP, add empty string or skip.
                           if (($candidate['finishReason'] ?? null) === 'STOP'){
                               $results[] = '';
                               continue;
                           }
                          throw new RuntimeException('Candidate ' . $index . ' has no content parts or function call.');
                      }
                 }


                 $part = $candidate['content']['parts'][0];

                if (isset($part['functionCall'])) {
                    $results[] = $this->handleFunctionCall($part['functionCall']);
                } elseif (isset($part['text'])) {
                    $text = $part['text'];
                    if ($this->json) {
                         $extracted = JsonExtractor::extract($text);
                        if ($extracted === null) {
                              // Attempt direct decode if MIME type was likely set
                              try {
                                  $results[] = json_decode($text, true, 512, JSON_THROW_ON_ERROR);
                                  continue; // Move to next candidate
                              } catch (JsonException $e) { /* Fall through */ }
                             throw new RuntimeException('Candidate ' . $index . ' response does not contain valid JSON.');
                        }
                         try {
                             $results[] = json_encode($extracted, JSON_THROW_ON_ERROR);
                         } catch (JsonException $e) {
                             throw new RuntimeException('Candidate ' . $index . ': Failed to re-encode extracted JSON: ' . $e->getMessage());
                         }
                    } else {
                        $results[] = $text;
                    }
                } else {
                    // Should have text or function call if we reached here
                    throw new RuntimeException('Candidate ' . $index . ' has no text or function call in the first part.');
                }

            } catch (RuntimeException $e) {
                // Add context to the exception and re-throw or collect errors
                throw new RuntimeException("Error processing candidate $index: " . $e->getMessage(), $e->getCode(), $e);
            }
        }

        return $results;
    }

    /**
     * Handles the 'functionCall' part of a Google API response.
     *
     * @param array $functionCall The 'functionCall' object from the response part.
     *                             Expected structure: { name: "...", args: { ... } }
     * @return array Formatted function call information.
     * @throws RuntimeException If the functionCall structure is invalid.
     */
    protected function handleFunctionCall(array $functionCall): array
    {
        if (!isset($functionCall['name']) || !isset($functionCall['args']) || !is_array($functionCall['args'])) {
            throw new RuntimeException('Invalid functionCall structure received from API.');
        }

        // Return a structured array consistent with how tool calls might be used
        return [
            'type' => 'function', // Indicate the type
            'function' => [
                'name' => $functionCall['name'],
                'arguments' => $functionCall['args'] // Google already provides args as an object/array
            ]
            // Google responses don't typically include an 'id' for the call itself like OpenAI.
        ];
    }
}
