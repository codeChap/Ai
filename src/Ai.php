<?php

declare(strict_types=1);

namespace codechap\ai;

use codechap\ai\Interfaces\ServiceInterface;

class Ai {

    private ServiceInterface $service;
    private static $serviceRegistry = [];

    /**
     * Constructor
     *
     * @param string $serviceType The type of AI service to use (case-insensitive)
     * @param string $apiKey The API key for authentication
     * @throws \InvalidArgumentException If service type or API key is empty, or service not found
     */
    public function __construct(
        string $serviceType,
        string $apiKey,
    ) {
        if (empty(trim($serviceType))) {
            throw new \InvalidArgumentException("Service name cannot be empty");
        }

        if (empty(trim($apiKey))) {
            throw new \InvalidArgumentException("API key cannot be empty");
        }

        // Normalize service type to lowercase
        $normalizedServiceType = strtolower($serviceType);
        
        // Warn if original had capital letters
        if ($serviceType !== $normalizedServiceType) {
            trigger_error("Service type '{$serviceType}' contains capital letters and has been automatically converted to lowercase.", E_USER_WARNING);
        }

        $this->loadServices();

        if (!isset(self::$serviceRegistry[$normalizedServiceType])) {
            throw new \InvalidArgumentException("Service '{$normalizedServiceType}' not found");
        }

        $serviceClass = self::$serviceRegistry[$normalizedServiceType];
        $this->service = new $serviceClass($apiKey);
    }

    /**
     * Load all services from the Services directory
     */
    private function loadServices(): void {
        if (empty(self::$serviceRegistry)) {
            $servicesPath = __DIR__ . '/Services';
            if (is_dir($servicesPath)) {
                foreach (glob($servicesPath . '/*Service.php') as $file) {
                    $filename = basename($file, '.php');
                    $serviceType = strtolower(str_replace('Service', '', $filename));
                    $serviceClass = 'codechap\\ai\\Services\\' . $filename;
                    self::registerService($serviceType, $serviceClass);
                }
            }
        }
    }

    /**
     * Register a new AI service
     */
    public static function registerService(string $serviceType, string $serviceClass): void {
        self::$serviceRegistry[$serviceType] = $serviceClass;
    }

    /**
     * Query the AI service
     *
     * @param string|array $prompt The prompt to send to the AI service
     * @return ServiceInterface The service instance for method chaining
     * @throws \InvalidArgumentException If prompt is empty or invalid
     * @throws \codechap\ai\Exceptions\ResponseException If the API request fails
     */
    public function query(string|array $prompt): ServiceInterface {
        return $this->service->query($prompt);
    }

    public function models(): array {
        return $this->service->models();
    }

    /**
     * Get a specific property from the service
     *
     * @param string $name The property name to get
     * @return mixed The value of the property
     * @throws \InvalidArgumentException If the property doesn't exist
     */
    public function get(string $name)
    {
        return $this->service->get($name);
    }

    /**
     * Set a specific property for the service
     *
     * @param string $name The property name to set
     * @param mixed $value The value to set
     * @return self Returns the current instance
     * @throws \InvalidArgumentException If the property doesn't exist
     */
    public function set(string $name, $value): self
    {
        $this->service->set($name, $value);
        return $this;
    }

    /**
     * Set the HTTP Client instance (useful for mocking)
     *
     * @param \codechap\ai\Interfaces\CurlInterface $curl
     * @return self
     */
    public function setCurl(\codechap\ai\Interfaces\CurlInterface $curl): self
    {
        if (method_exists($this->service, 'setCurl')) {
            $this->service->setCurl($curl);
        }
        return $this;
    }
}
