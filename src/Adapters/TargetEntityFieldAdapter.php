<?php declare(strict_types = 1);

namespace Nettrine\Hydrator\Adapters;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Nettrine\Hydrator\Arguments\FieldArgs;

class TargetEntityFieldAdapter implements IFieldAdapter
{

	/** @var EntityManagerInterface */
	private $em;

	public function __construct(EntityManagerInterface $em)
	{
		$this->em = $em;
	}

	public function isWorkable(FieldArgs $args): bool
	{
		$mapping = $args->metadata->getMapping($args->field);

		return isset($mapping['targetEntity']) && $mapping['type'] !== ClassMetadataInfo::MANY_TO_MANY;
	}

	public function work(FieldArgs $args): void
	{
		$mapping = $args->metadata->getMapping($args->field);
		$targetEntity = $mapping['targetEntity'];

		if ($args->value instanceof $targetEntity) {
			return;
		}
		if ($args->value === null) {
			return;
		}
		if (is_array($args->value)) {
			// TODO: settings
			$args->value = $args->hydrateToFields($targetEntity, $args->value);
			return;
		}

		$args->value = $this->em->getRepository($targetEntity)->find($args->value);
	}

}
