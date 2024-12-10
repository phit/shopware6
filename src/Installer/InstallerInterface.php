<?php

declare(strict_types=1);

namespace UnzerPayment6\Installer;

use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\DeactivateContext;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;

interface InstallerInterface
{
    public function install(InstallContext $context, ?object $publicFileSystem): void;

    public function update(UpdateContext $context, ?object $publicFileSystem): void;

    public function uninstall(UninstallContext $context): void;

    public function activate(ActivateContext $context): void;

    public function deactivate(DeactivateContext $context): void;
}
