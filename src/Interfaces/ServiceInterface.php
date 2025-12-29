<?php

namespace codechap\ai\Interfaces;

/**
 * Interface AIServiceInterface
 *
 * Defines the contract for AI service implementations that handle query operations.
 * This interface ensures consistent query functionality across different AI services.
 */
interface ServiceInterface
{
    /**
     * Sends a query to the AI service.
     *
     * @param string|array $prompts The prompt(s) to send to the AI service
     * @return self Returns the current instance for method chaining
     * @throws \InvalidArgumentException If prompts are empty or invalid
     * @throws \codechap\ai\Exceptions\ResponseException If the API request fails
     */
    public function query(string|array $prompts): self;

    /**
     * Gets a single response from the AI service.
     *
     * @return array|string The first/primary response from the AI
     * @throws \RuntimeException If JSON parsing fails (when JSON mode is enabled)
     */
    public function one(): array | string;

    /**
     * Gets all responses from the AI service.
     *
     * @return array Array of all responses from the AI
     * @throws \RuntimeException If JSON parsing fails (when JSON mode is enabled)
     */
    public function all(): array;

    /**
     * Gets the value of a property
     *
     * @param string $name The name of the property
     * @return mixed The value of the property
     * @throws \InvalidArgumentException If the property doesn't exist
     */
    public function get(string $name);

    /**
     * Sets the value of a property
     *
     * @param string $name The name of the property
     * @param mixed $value The value to set
     * @return self Returns the current instance for method chaining
     * @throws \InvalidArgumentException If the property doesn't exist
     */
    public function set(string $name, mixed $value): self;

    /**
     * Gets the models available for the AI service.
     *
     * @param string|null $column The column to sort by.
     * @return array Array of models available for the AI service
     */
    public function models(?string $column = null): array;
}
