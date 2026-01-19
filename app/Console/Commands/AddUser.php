<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\System\Language;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

class AddUser extends Command implements PromptsForMissingInput
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:add
        {name : The user name}
        {email : The user email address}
        {--password= : The user password (will prompt if omitted)}
        {--admin : Mark the user as an administrator}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new user';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = (string) $this->argument('name');
        $email = (string) $this->argument('email');
        $passwordValue = $this->resolvePassword();

        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $passwordValue,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => ['required', 'string', Password::default()],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        $user = new User;
        $user->name = $name;
        $user->email = $email;
        $user->password = Hash::make($passwordValue);
        $user->is_admin = (bool) $this->option('admin');
        $user->language_id = $this->resolveLanguageId();
        $user->save();

        $this->info(sprintf('User "%s" created.', $user->email));

        return self::SUCCESS;
    }

    /**
     * Prompt for missing input arguments using the returned questions.
     *
     * @return array<string, callable>
     */
    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            'name' => fn (): string => text(label: 'Name', required: true),
            'email' => fn (): string => text(label: 'Email address', required: true),
        ];
    }

    private function resolvePassword(): string
    {
        $passwordValue = $this->option('password');

        if (is_string($passwordValue) && $passwordValue !== '') {
            return $passwordValue;
        }

        $passwordValue = password(label: 'Password', required: true);
        $confirmation = password(label: 'Confirm password', required: true);

        while ($confirmation !== $passwordValue) {
            $this->error('Passwords do not match. Please try again.');

            $passwordValue = password(label: 'Password', required: true);
            $confirmation = password(label: 'Confirm password', required: true);
        }

        return $passwordValue;
    }

    private function resolveLanguageId(): int
    {
        $languageCode = (string) config('language.english', Language::ENGLISH);
        $language = Language::query()->where('code', $languageCode)->first();

        if ($language === null) {
            $language = new Language;
            $language->code = $languageCode;
            $language->save();
        }

        return (int) $language->getKey();
    }
}
