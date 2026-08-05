<?php

namespace Tests\Unit\Cv;

use App\Support\CvTextFormatter;
use Tests\TestCase;

class CvTextFormatterTest extends TestCase
{
    public function test_it_removes_leading_dash_bullet_and_dot(): void
    {
        $text = implode(PHP_EOL, [
            '— Premier paragraphe',
            '• Deuxième paragraphe',
            '- Troisième paragraphe',
            '. Quatrième paragraphe',
        ]);

        $this->assertSame(
            implode(PHP_EOL, [
                'Premier paragraphe',
                'Deuxième paragraphe',
                'Troisième paragraphe',
                'Quatrième paragraphe',
            ]),
            CvTextFormatter::clean($text)
        );
    }

    public function test_it_preserves_normal_sentence_punctuation(): void
    {
        $text = 'Développement web. Laravel — MySQL.';

        $this->assertSame(
            $text,
            CvTextFormatter::clean($text)
        );
    }

    public function test_cv_css_forces_the_requested_indent(): void
    {
        $css = file_get_contents(
            resource_path('css/cv-print.css')
        );

        $this->assertIsString($css);
        $this->assertStringContainsString(
            'text-indent: 1.25cm !important;',
            $css
        );
    }
}
