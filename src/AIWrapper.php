<?php

declare(strict_types=1);

namespace Codechap\Aiwrapper;

use Codechap\Aiwrapper\Services\OpenAiService;
use Codechap\Aiwrapper\Services\AnthropicService;
use Codechap\Aiwrapper\Services\XaiService;

class AIWrapper {
    private $service;
    private static $serviceRegistry = [];

    public function __construct(
        private readonly string $serviceType,
        private readonly string $apiKey,
    ) {
        $this->loadServices();
        
        if (!isset(self::$serviceRegistry[$serviceType])) {
            // Try to load the service directly from the Services directory
            $serviceClass = 'Codechap\\Aiwrapper\\Services\\' . ucfirst($serviceType) . 'Service';
            if (class_exists($serviceClass)) {
                self::registerService($serviceType, $serviceClass);
            } else {
                throw new \InvalidArgumentException("Invalid service type: {$serviceType}");
            }
        }

        $serviceClass = self::$serviceRegistry[$serviceType];
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
                    $serviceClass = 'Codechap\\Aiwrapper\\Services\\' . $filename;
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
     */
    public function query(string $prompt): string {
        return $this->service->query($prompt);
    }
}