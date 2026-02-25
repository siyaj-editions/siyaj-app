<?php

namespace App\DataFixtures;

use App\Entity\Author;
use App\Entity\Book;
use App\Entity\User;
use App\Enum\BookFormat;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // Créer les utilisateurs
        $this->createUser($manager, 'admin@siyag.com', 'admin123', 'Admin', 'SIYAG', ['ROLE_ADMIN']);
        $this->createUser($manager, 'client@test.com', 'client123', 'Jean', 'Dupont', ['ROLE_USER']);

        // Créer les auteurs
        $authors = $this->createAuthors($manager);

        // Créer les livres
        $this->createBooks($manager, $authors);

        $manager->flush();
    }

    private function createUser(ObjectManager $manager, string $email, string $password, string $firstname, string $lastname, array $roles): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setFirstname($firstname);
        $user->setLastname($lastname);
        $user->setRoles($roles);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));

        $manager->persist($user);

        return $user;
    }

    private function createAuthors(ObjectManager $manager): array
    {
        $authorsData = [
            ['name' => 'Victor Hugo', 'bio' => 'Poète, dramaturge et prosateur romantique français.'],
            ['name' => 'Émile Zola', 'bio' => 'Écrivain et journaliste français, chef de file du naturalisme.'],
            ['name' => 'Marcel Proust', 'bio' => 'Écrivain français, auteur d\'À la recherche du temps perdu.'],
            ['name' => 'Gustave Flaubert', 'bio' => 'Romancier français du XIXe siècle.'],
            ['name' => 'Jules Verne', 'bio' => 'Écrivain français, pionnier de la science-fiction.'],
            ['name' => 'Alexandre Dumas', 'bio' => 'Écrivain français, auteur de romans d\'aventures.'],
            ['name' => 'Honoré de Balzac', 'bio' => 'Romancier français, auteur de La Comédie humaine.'],
            ['name' => 'Albert Camus', 'bio' => 'Écrivain et philosophe français, prix Nobel de littérature.'],
        ];

        $authors = [];
        foreach ($authorsData as $data) {
            $author = new Author();
            $author->setName($data['name']);
            $author->setBio($data['bio']);
            $manager->persist($author);
            $authors[$data['name']] = $author;
        }

        return $authors;
    }

    private function createBooks(ObjectManager $manager, array $authors): void
    {
        $booksData = [
            ['title' => 'Les Misérables', 'isbn' => '978-2070409228', 'description' => 'Fresque sociale et historique du XIXe siècle.', 'price' => 1599, 'format' => BookFormat::PHYSICAL, 'stock' => 10, 'authors' => ['Victor Hugo']],
            ['title' => 'Notre-Dame de Paris', 'isbn' => '978-2070413089', 'description' => 'Roman historique au XVe siècle.', 'price' => 1299, 'format' => BookFormat::PHYSICAL, 'stock' => 15, 'authors' => ['Victor Hugo']],
            ['title' => 'Germinal', 'isbn' => '978-2070360338', 'description' => 'Roman sur la condition des mineurs.', 'price' => 999, 'format' => BookFormat::DIGITAL, 'stock' => null, 'authors' => ['Émile Zola']],
            ['title' => 'L\'Assommoir', 'isbn' => '978-2070368976', 'description' => 'Roman naturaliste sur l\'alcoolisme.', 'price' => 899, 'format' => BookFormat::DIGITAL, 'stock' => null, 'authors' => ['Émile Zola']],
            ['title' => 'Du côté de chez Swann', 'isbn' => '978-2070364701', 'description' => 'Premier tome d\'À la recherche du temps perdu.', 'price' => 1499, 'format' => BookFormat::PHYSICAL, 'stock' => 8, 'authors' => ['Marcel Proust']],
            ['title' => 'Madame Bovary', 'isbn' => '978-2070413119', 'description' => 'Roman sur l\'ennui d\'une femme de médecin.', 'price' => 1199, 'format' => BookFormat::PHYSICAL, 'stock' => 12, 'authors' => ['Gustave Flaubert']],
            ['title' => 'L\'Éducation sentimentale', 'isbn' => '978-2070368679', 'description' => 'La vie de la jeunesse parisienne.', 'price' => 1299, 'format' => BookFormat::PHYSICAL, 'stock' => 7, 'authors' => ['Gustave Flaubert']],
            ['title' => 'Vingt mille lieues sous les mers', 'isbn' => '978-2253006329', 'description' => 'Aventures sous-marines avec le Nautilus.', 'price' => 999, 'format' => BookFormat::PHYSICAL, 'stock' => 20, 'authors' => ['Jules Verne']],
            ['title' => 'Le Tour du monde en quatre-vingts jours', 'isbn' => '978-2253006336', 'description' => 'Le pari de Phileas Fogg.', 'price' => 899, 'format' => BookFormat::DIGITAL, 'stock' => null, 'authors' => ['Jules Verne']],
            ['title' => 'Les Trois Mousquetaires', 'isbn' => '978-2253098232', 'description' => 'Les aventures de d\'Artagnan.', 'price' => 1499, 'format' => BookFormat::PHYSICAL, 'stock' => 15, 'authors' => ['Alexandre Dumas']],
            ['title' => 'Le Comte de Monte-Cristo', 'isbn' => '978-2253098232', 'description' => 'Histoire de vengeance d\'Edmond Dantès.', 'price' => 1699, 'format' => BookFormat::PHYSICAL, 'stock' => 10, 'authors' => ['Alexandre Dumas']],
            ['title' => 'Le Père Goriot', 'isbn' => '978-2070369119', 'description' => 'Roman social sur un père dévoué.', 'price' => 1099, 'format' => BookFormat::PHYSICAL, 'stock' => 9, 'authors' => ['Honoré de Balzac']],
            ['title' => 'Eugénie Grandet', 'isbn' => '978-2253004905', 'description' => 'Roman sur l\'avarice.', 'price' => 999, 'format' => BookFormat::DIGITAL, 'stock' => null, 'authors' => ['Honoré de Balzac']],
            ['title' => 'L\'Étranger', 'isbn' => '978-2070360024', 'description' => 'Roman philosophique sur l\'absurde.', 'price' => 899, 'format' => BookFormat::DIGITAL, 'stock' => null, 'authors' => ['Albert Camus']],
            ['title' => 'La Peste', 'isbn' => '978-2070360420', 'description' => 'Roman allégorique sur une épidémie.', 'price' => 999, 'format' => BookFormat::DIGITAL, 'stock' => null, 'authors' => ['Albert Camus']],
        ];

        foreach ($booksData as $data) {
            $book = new Book();
            $book->setTitle($data['title']);
            $book->setIsbn($data['isbn']);
            $book->setDescription($data['description']);
            $book->setPriceCents($data['price']);
            $book->setFormat($data['format']);
            $book->setStock($data['stock']);
            $book->setIsActive(true);
            $book->setPublishedAt(new \DateTimeImmutable());

            foreach ($data['authors'] as $authorName) {
                if (isset($authors[$authorName])) {
                    $book->addAuthor($authors[$authorName]);
                }
            }

            $manager->persist($book);
        }
    }
}
