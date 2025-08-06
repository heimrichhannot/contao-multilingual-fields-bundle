<?php

namespace HeimrichHannot\MultilingualFieldsBundle\Util;

class DcaUtil
{
    public function setFieldsToReadOnly(&$dca, array $config = []): void
    {
        $skipFields = $config['skipFields'] ?? [];
        $fields = $config['fields'] ?? [];

        foreach ($dca['fields'] as $field => &$data) {
            if (!empty($fields)) {
                if (!\in_array($field, $fields)) {
                    continue;
                }
            } elseif (\in_array($field, $skipFields)) {
                continue;
            }

            switch ($data['inputType']) {
                case 'checkbox':
                case 'radio':
                case 'radioTable':
                    $data['eval']['disabled'] = true;

                    break;

                case 'select':
                case 'imageSize':
                    $data['eval']['readonly'] = true;
                    $data['eval']['class'] = 'readonly';

                    break;

                case 'fileTree':
                case 'metaWizard':
                case 'tagsinput':
                    $data['eval']['readonly'] = true;
                    $data['eval']['tl_class'] = $data['eval']['tl_class'].' readonly';

                    break;

                case 'multiColumnEditor':
                    $data['eval']['readonly'] = true;

                    $this->setFieldsToReadOnly($data['eval']['multiColumnEditor'], $config);

                    break;

                default:
                    $data['eval']['readonly'] = true;
                    break;
            }
        }
    }
}