<?php

namespace App\Story;

use App\Factory\UserFactory;
use Zenstruck\Foundry\Attribute\AsFixture;
use Zenstruck\Foundry\Story;

#[AsFixture(name: 'main')]
final class AppStory extends Story
{
    public function build(): void
    {
        $mariolina = UserFactory::new([
            'firstname' => 'Mariolina',
            'lastname' => 'Baruselli',
            'email' => 'mariolina.baruselli@europrofiligroup.it',
            'roles' => ['ROLE_STAFF']
        ])->create();

        $diego = UserFactory::new([
            'firstname' => 'Diego',
            'lastname' => 'Poggiana',
            'email' => 'diego.poggiana@europrofiligroup.it',
            'roles' => ['ROLE_BOSS'],
            'parentUser' => $mariolina
        ])->create();

        $marco = UserFactory::new([
            'firstname' => 'Marco',
            'lastname' => 'Natalini',
            'email' => 'marco.natalini@europrofiligroup.it',
            'parentUser' => $diego
        ])->create();

        $roberto = UserFactory::new([
            'firstname' => 'Roberto',
            'lastname' => 'Sabadini',
            'email' => 'roberto.sabadini@europrofiligroup.it',
            'parentUser' => $diego
        ])->create();


//        UserFactory::createMany(5, [
//            'parentUser' => $diego
//        ]);
    }
}
