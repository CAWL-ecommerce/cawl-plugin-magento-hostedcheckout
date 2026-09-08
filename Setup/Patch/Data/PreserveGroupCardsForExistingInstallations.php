<?php
declare(strict_types=1);

namespace Cawl\HostedCheckout\Setup\Patch\Data;

use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Cawl\HostedCheckout\Gateway\Config\Config;
use Cawl\HostedCheckout\Ui\ConfigProvider;
use Cawl\PaymentCore\Model\Config\WorldlineConfig;

/**
 * "Group Cards" ships enabled by default (see etc/config.xml), which suits the French market where every card is
 * cobranded with the local CB scheme. Installations that already exist must keep the previous behaviour, so this
 * patch writes the old default explicitly for them.
 */
class PreserveGroupCardsForExistingInstallations implements DataPatchInterface
{
    private const GROUP_CARDS_PATH = 'payment/' . ConfigProvider::HC_CODE . '/' . Config::ENABLE_GROUP_CARDS;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var WriterInterface
     */
    private $configWriter;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        WriterInterface $configWriter
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->configWriter = $configWriter;
    }

    public function apply(): self
    {
        $this->moduleDataSetup->startSetup();

        if ($this->hasDefaultScopeValue(self::GROUP_CARDS_PATH)) {
            $this->moduleDataSetup->endSetup();
            return $this;
        }

        if ($this->configExists(WorldlineConfig::MERCHANT_ID)) {
            $this->configWriter->save(self::GROUP_CARDS_PATH, 0);
        }

        $this->moduleDataSetup->endSetup();

        return $this;
    }

    /**
     * Whether the merchant already chose a value for the given path in the default scope
     */
    private function hasDefaultScopeValue(string $path): bool
    {
        $connection = $this->moduleDataSetup->getConnection();

        $select = $connection->select()
            ->from($this->moduleDataSetup->getTable('core_config_data'), 'config_id')
            ->where('path = ?', $path)
            ->where('scope = ?', 'default')
            ->limit(1);

        return (bool) $connection->fetchOne($select);
    }

    /**
     * Whether the given path is stored in any scope, used here to tell an existing installation from a fresh one
     */
    private function configExists(string $path): bool
    {
        $connection = $this->moduleDataSetup->getConnection();

        $select = $connection->select()
            ->from($this->moduleDataSetup->getTable('core_config_data'), 'config_id')
            ->where('path = ?', $path)
            ->limit(1);

        return (bool) $connection->fetchOne($select);
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}
