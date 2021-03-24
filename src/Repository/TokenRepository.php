<?php

namespace Moukail\AppleWalletPassBundle\Repository;

use Moukail\AppleWalletPassBundle\Entity\PassInterface;
use Moukail\AppleWalletPassBundle\Entity\Token;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Ramsey\Uuid\Uuid;

/**
 * @method Token[] findAll()
 * @method Token[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Token::class);
    }

    /**
     * @param PassInterface $pass
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function findOrMake(PassInterface $pass): Token
    {
        /** @var Token $applePassToken */
        $appleToken = $this->findOneBy(['pass' => $pass]);

        if (!$appleToken) {

            $token = Uuid::uuid5(Uuid::uuid4(), 'DBF');
            $appleToken = new Token($pass, new \DateTimeImmutable('1 year'), $token);

            $this->getEntityManager()->persist($appleToken);
            $this->getEntityManager()->flush();
        }

        return $appleToken;
    }
}
