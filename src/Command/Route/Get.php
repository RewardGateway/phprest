<?php

namespace Phprest\Command\Route;

use Closure;
use Phprest\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Get extends Command
{
    protected Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('routes:get')
            ->setDescription('Get registered routes.');
    }

    /**
     * @return void
     *
     * Return type of this method doesn't match to extended method.
     * Keeping it generic to avoid future BC issues.
     *
     * @phpstan-ignore-next-line
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $routes         = [];
        $routingTable   = $this->app->getRouter()->getRoutingTable();

        usort($routingTable, static function ($a, $b) {
            return ($a['route'] < $b['route']) ? -1 : 1;
        });

        foreach ($routingTable as $routingTableRecord) {
            if ($routingTableRecord['handler'] instanceof Closure) {
                $routes[] = [
                    $routingTableRecord['method'],
                    $routingTableRecord['route'],
                    'Closure',
                ];
            } else {
                $routes[] = [
                    $routingTableRecord['method'],
                    $routingTableRecord['route'],
                    $routingTableRecord['handler'],
                ];
            }
        }

        $table = new Table($output);
        $table
            ->setHeaders(['Method', 'Route', 'Handler'])
            ->setRows($routes);
        $table->render();
    }
}
