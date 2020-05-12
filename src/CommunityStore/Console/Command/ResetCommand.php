<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Console\Command;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderList;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductList;
use Concrete\Package\CommunityStore\Src\CommunityStore\Discount\DiscountRuleList;

class ResetCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('cstore:reset')
            ->setDescription('Reset the Community Store package')
            ->addArgument('type', InputArgument::OPTIONAL, 'Type of reset')
            ->setHelp(<<<EOT
Returns codes:
  0 operation completed successfully
  1 errors occurred
EOT
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $rc = 0;

        $operationType = $input->getArgument('type');

        if (!$operationType) {
            throw new Exception("You have to specify the type of reset to run this command");
        }

        if ('all' == $operationType) {
            $typeMessage = t('Are you sure you want to remove all products, orders and discounts from Community Store? (y/n)');
        }

        if ('products' == $operationType) {
            $typeMessage = t('Are you sure you want to remove all products from Community Store? (y/n)');
        }

        if ('orders' == $operationType) {
            $typeMessage = t('Are you sure you want to remove all orders from Community Store? (y/n)');
        }

        if ('discounts' == $operationType) {
            $typeMessage = t('Are you sure you want to remove all discounts from Community Store? (y/n)');
        }

        $confirmQuestion = new ConfirmationQuestion(
            $typeMessage,
            false
        );

        if (!$this->getHelper('question')->ask($input, $output, $confirmQuestion)) {
            throw new Exception(t("Operation aborted."));
        }

        if ('all' == $operationType || 'orders' == $operationType) {
            $orderList = new OrderList();
            $orders = $orderList->getResults();
            $orderCount = count($orders);

            foreach ($orders as $order) {
                $order->remove();
            }
            $output->writeln('<info>' . t2('%d order deleted', '%d orders deleted', $orderCount) . '</info>');
        }

        if ('all' == $operationType || 'products' == $operationType) {
            $productList = new ProductList();
            $productList->setActiveOnly(false);
            $productList->setShowOutOfStock(true);
            $products = $productList->getResults();
            $productCount = count($products);

            foreach ($products as $product) {
                $product->remove();
            }
            $output->writeln('<info>' . t2('%d product deleted', '%d products deleted', $productCount) . '</info>');
        }

        if ('all' == $operationType || 'discounts' == $operationType) {
            $discountList = new DiscountRuleList();
            $discounts = $discountList->getResults();

            $discountCount = 0;

            foreach ($discounts as $discount) {
                $discount->delete();
                ++$discountCount;
            }

            $output->writeln('<info>' . t2('%d discount deleted', '%d discounts deleted', $discountCount) . '</info>');
        }

        return $rc;
    }
}
