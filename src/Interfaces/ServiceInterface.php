<?php

namespace Codechap\Aiwrapper\Interfaces;

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
     */
    public function query(string|array $prompts): self;

    /**
     * Gets a single response from the AI service.
     *
     * @return string The first/primary response from the AI
     */
    public function one(): string;

    /**
     * Gets all responses from the AI service.
     *
     * @return array Array of all responses from the AI
     */
    public function all(): array;

    /**
     * Gets the value of a property
     *
     * @param string $name The name of the property
     * @return mixed The value of the property
     * @throws \Exception If the property doesn't exist
     */
    public function get(string $name);

    /**
     * Sets the value of a property
     *
     * @param string $name The name of the property
     * @param mixed $value The value to set
     * @return self Returns the current instance for method chaining
     * @throws \Exception If the property doesn't exist
     */
    public function set(string $name, mixed $value): self;
} 