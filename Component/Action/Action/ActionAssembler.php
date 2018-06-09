<?php

namespace Labudzinski\TestFrameworkBundle\Component\Action\Action;

use Labudzinski\TestFrameworkBundle\Component\Action\Condition\Configurable as ConfigurableCondition;
use Labudzinski\TestFrameworkBundle\Component\Action\Model\AbstractAssembler;
use Labudzinski\TestFrameworkBundle\Component\ConfigExpression\ExpressionFactoryInterface as ConditionFactoryInterface;

class ActionAssembler extends AbstractAssembler
{
    const PARAMETERS_KEY = 'parameters';
    const BREAK_ON_FAILURE_KEY = 'break_on_failure';
    const ACTIONS_KEY = 'actions';
    const CONDITIONS_KEY = 'conditions';

    /**
     * @var ActionFactoryInterface
     */
    protected $actionFactory;

    /**
     * @var ConditionFactoryInterface
     */
    protected $conditionFactory;

    /**
     * @param ActionFactoryInterface $actionFactory
     * @param ConditionFactoryInterface $conditionFactory
     */
    public function __construct(ActionFactoryInterface $actionFactory, ConditionFactoryInterface $conditionFactory)
    {
        $this->actionFactory = $actionFactory;
        $this->conditionFactory  = $conditionFactory;
    }

    /**
     * Allowed formats:
     *
     * array(
     *     'conditions' => array(<condition_data>),
     *     'actions' => array(
     *         array(<first_action_data>),
     *         array(<second_action_data>),
     *         ...
     *     )
     * )
     *
     * or
     *
     * array(
     *     array(<first_action_data>),
     *     array(<second_action_data>),
     *     ...
     * )
     *
     * @param array $configuration
     * @return ActionInterface
     */
    public function assemble(array $configuration)
    {
        /** @var TreeExecutor $treeAction */
        $treeAction = $this->actionFactory->create(
            TreeExecutor::ALIAS,
            array(),
            $this->createConfigurableCondition($configuration)
        );

        $actionsConfiguration = $this->getOption($configuration, self::ACTIONS_KEY, $configuration);
        foreach ($actionsConfiguration as $actionConfiguration) {
            if ($this->isService($actionConfiguration)) {
                $options = (array)$this->getEntityParameters($actionConfiguration);
                $breakOnFailure = $this->getOption($options, self::BREAK_ON_FAILURE_KEY, true);

                $actionType = $this->getEntityType($actionConfiguration);
                $serviceName = $this->getServiceName($actionType);

                if ($serviceName == TreeExecutor::ALIAS) {
                    $action = $this->assemble($options);
                } else {
                    $actionParameters = $this->getOption($options, self::PARAMETERS_KEY, $options);
                    $passedActionParameters = $this->passConfiguration($actionParameters);
                    $action = $this->actionFactory->create(
                        $serviceName,
                        $passedActionParameters,
                        $this->createConfigurableCondition($options)
                    );
                }

                $treeAction->addAction($action, $breakOnFailure);
            }
        }

        return $treeAction;
    }

    /**
     * @param array $conditionConfiguration
     * @return null|ConfigurableCondition
     */
    protected function createConfigurableCondition(array $conditionConfiguration)
    {
        $condition = null;
        $conditionConfiguration = $this->getOption($conditionConfiguration, self::CONDITIONS_KEY, null);
        if ($conditionConfiguration) {
            $condition = $this->conditionFactory->create(ConfigurableCondition::ALIAS, $conditionConfiguration);
        }

        return $condition;
    }
}
