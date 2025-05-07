<?php

namespace App\Utils;

/**
 * Class SwaggerSecurityFilter
 * 
 * A filter to remove security components from the Swagger specification
 */
class SwaggerSecurityFilter
{
    /**
     * Filter the swagger documentation to remove security components
     * 
     * @param array $docs The original swagger documentation
     * @return array The modified swagger documentation
     */
    public function __invoke(array $docs): array
    {
        // Remove security schemes
        if (isset($docs['components']) && isset($docs['components']['securitySchemes'])) {
            unset($docs['components']['securitySchemes']);
        }
        
        // Remove security requirements from operations
        if (isset($docs['paths']) && is_array($docs['paths'])) {
            foreach ($docs['paths'] as $path => $pathData) {
                if (is_array($pathData)) {
                    foreach ($pathData as $method => $operation) {
                        if (is_array($operation) && isset($operation['security'])) {
                            unset($docs['paths'][$path][$method]['security']);
                        }
                    }
                }
            }
        }
        
        // Remove global security
        if (isset($docs['security'])) {
            unset($docs['security']);
        }
        
        return $docs;
    }
} 