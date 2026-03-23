<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsDoctrineListener(event: Events::postUpdate)]
class CommentApprovedSubscriber
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly UrlGeneratorInterface $urlGenerator,
        #[Autowire('%env(EMAIL_NOTIFICATIONS_FROM)%')]
        private readonly string $emailFrom,
    ) {
    }

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Comment) {
            return;
        }

        $changeSet = $args->getObjectManager()->getUnitOfWork()->getEntityChangeSet($entity);

        // Vérifier que isApproved est passé de false à true
        if (!isset($changeSet['isApproved'])) {
            return;
        }

        /** @var array{0: bool, 1: bool} $change */
        $change = $changeSet['isApproved'];

        if (false !== $change[0] || true !== $change[1]) {
            return;
        }

        $user = $entity->getUser();
        $article = $entity->getArticle();

        if (null === $user || null === $article) {
            return;
        }

        $articleUrl = $this->urlGenerator->generate(
            'app_article_show',
            ['slug' => $article->getSlug()],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );

        $email = (new TemplatedEmail())
            ->from(new Address($this->emailFrom, 'MichaelJPitz.com'))
            ->to((string) $user->getEmail())
            ->subject('Votre commentaire a été approuvé')
            ->htmlTemplate('email/comment_approved.html.twig')
            ->context([
                'userName' => $user->getUserName(),
                'articleTitle' => $article->getTitle(),
                'articleUrl' => $articleUrl,
                'commentContent' => $entity->getContent(),
            ]);

        $this->mailer->send($email);
    }
}
