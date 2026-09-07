<?php

declare(strict_types=1);

namespace MovesOSTests\Unit;

use PHPUnit\Framework\TestCase;

final class OrganicEditorConsolidationTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__, 2);
    }

    public function testStudioUsesOnlyCanonicalOrganicDistribution(): void
    {
        self::assertFileExists($this->root . '/organic/editor/organic-editor.min.js');
        self::assertFileExists($this->root . '/organic/editor/organic-editor.min.css');
        self::assertDirectoryDoesNotExist($this->root . '/container/apps/studio/default/assets/vendor/organic-editor');

        $layout = file_get_contents($this->root . '/container/apps/studio/default/layouts/studio.php');
        $pipeline = file_get_contents($this->root . '/source/Services/Assets/AssetBuilder.php');
        self::assertStringContainsString("url('/organic/editor/organic-editor.min.js')", $layout);
        self::assertStringContainsString("/organic/editor/organic-editor.min.css", $pipeline);
        self::assertStringNotContainsString("/vendor/organic-editor/", $pipeline);
    }

    public function testPersistenceSeparatesPagesPostsAndTemplates(): void
    {
        $bootstrap = file_get_contents($this->root . '/container/apps/studio/default/assets/js/editor.js');
        self::assertStringContainsString('blog/post" ? "post"', $bootstrap);
        self::assertStringContainsString('"slide" ? "template"', $bootstrap);
        self::assertStringContainsString('moves-studio:organic:', $bootstrap);
        self::assertStringContainsString('match?.[2] || "new"', $bootstrap);
        self::assertStringContainsString('moves:form-saved', $bootstrap);
        self::assertStringContainsString('storage.remove("organic-editor-v0100:autosave")', $bootstrap);

        $formHandler = file_get_contents($this->root . '/container/apps/studio/default/assets/js/scripts.js');
        self::assertStringContainsString('new CustomEvent("moves:form-saved"', $formHandler);
    }
}
