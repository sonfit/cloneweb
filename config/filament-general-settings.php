<?php

use Joaopaulolndev\FilamentGeneralSettings\Enums\TypeFieldEnum;

return [
    'show_application_tab' => true,
    'show_logo_and_favicon' => false,
    'show_analytics_tab' => false,
    'show_seo_tab' => false,
    'show_email_tab' => false,
    'show_social_networks_tab' => false,
    'expiration_cache_config_time' => 60,
    'show_custom_tabs'=> true,
    'custom_tabs' => [
        'ChatGPT_configs' => [
            'label' => 'ChatGPT Configs',
            'icon' => 'heroicon-o-plus-circle',
            'columns' => 1,
            'fields' => [
                'key_api' => [
                    'type' => TypeFieldEnum::Text->value,
                    'label' => 'Key API',
                    'placeholder' => 'sk-...',
                    'required' => false,
                    'rules' => 'string|max:255',
                ],
                'model_api' => [
                    'type' => TypeFieldEnum::Text->value,
                    'label' => 'Model',
                    'placeholder' => 'gpt-4-turbo',
                    'required' => false,
                    'rules' => 'string|max:255',
                ],
                'temperature_api' => [
                    'type' => TypeFieldEnum::Text->value,
                    'label' => 'Temperature',
                    'placeholder' => '0.5',
                    'required' => false,
                    'rules' => 'numeric|max:255',
                ],
                'max_tokens_api' => [
                    'type' => TypeFieldEnum::Text->value,
                    'label' => 'Max tokens',
                    'placeholder' => '300',
                    'required' => false,
                    'rules' => 'numeric|max:500',
                ],
                'select_prompt' => [
                    'type' => TypeFieldEnum::Select->value,
                    'label' => 'Select prompt',
                    'placeholder' => 'Select',
                    'required' => true,
                    'options' => array_filter([
                        'prompt_1' => 'Prompt 1',
                        'prompt_2' => 'Prompt 2',
                        'prompt_3' => 'Prompt 3',
                    ]),
                ],
                'prompt_1' => [
                    'type' => TypeFieldEnum::Textarea->value,
                    'label' => 'Prompt 1',
                    'placeholder' => 'Prompt 1',
                    'required' => true,
                    'rows' => '3'
                ],
                'prompt_2' => [
                    'type' => TypeFieldEnum::Textarea->value,
                    'label' => 'Prompt 2',
                    'placeholder' => 'Prompt 2',
                    'required' => false,
                    'rows' => '3'
                ],
                'prompt_3' => [
                    'type' => TypeFieldEnum::Textarea->value,
                    'label' => 'Prompt 3',
                    'placeholder' => 'Prompt 3',
                    'required' => false,
                    'rows' => '3'
                ],

            ]
        ],
    ]
];
