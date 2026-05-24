<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Domain Entities
use App\Domain\Role\Entities\RoleEntity;
use App\Domain\User\Entities\UserEntity;
use App\Domain\Module\Entities\ModuleEntity;
use App\Domain\Settings\Entities\SettingEntity;

// Domain Services
use App\Domain\Role\Services\RoleService;
use App\Domain\User\Services\UserService;
use App\Domain\Module\Services\ModuleService;
use App\Domain\Settings\Services\SettingService;
use App\Domain\User\Services\AuthenticationService;

// Domain Repository Interfaces
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\Role\Repositories\RoleRepositoryInterface;
use App\Domain\Module\Repositories\ModuleRepositoryInterface;
use App\Domain\Settings\Repositories\SettingRepositoryInterface;

// Infrastructure Repository Implementations
use App\Infrastructure\Persistence\Eloquent\EloquentUserRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentRoleRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentModuleRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentSettingRepository;

// Application UseCases - Auth
use App\Application\User\UseCases\LoginUserUseCase;
use App\Application\User\UseCases\LogoutUserUseCase;
use App\Application\User\UseCases\RegisterUserUseCase;
use App\Application\User\Actions\LoginAction;
use App\Application\User\Actions\LogoutAction;
use App\Application\User\Actions\RegisterAction;
use App\Application\User\Actions\ForgotPasswordAction;
use App\Application\User\Actions\ResetPasswordAction;

// Application Actions - Module
use App\Application\Module\Actions\GetModuleAction;
use App\Application\Module\Actions\CreateModuleAction;
use App\Application\Module\Actions\UpdateModuleAction;
use App\Application\Module\Actions\DeleteModuleAction;

// Application Actions - Role
use App\Application\Role\Actions\CreateRoleAction;
use App\Application\Role\Actions\UpdateRoleAction;

// Application Actions - Settings
use App\Application\Settings\Actions\UpdateSettingAction;
use App\Application\Settings\Actions\GetSettingsByGroupAction;
use App\Application\Settings\Actions\UpdateMultipleSettingsAction;

class DomainServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register Domain Services
        $this->app->singleton(AuthenticationService::class);
        $this->app->singleton(UserService::class);
        $this->app->singleton(SettingService::class);
        $this->app->singleton(RoleService::class);
        $this->app->singleton(ModuleService::class);
        
        // Register Repositories
        $this->app->singleton(UserRepositoryInterface::class, function ($app) {
            return new EloquentUserRepository(app(\App\Models\User::class));
        });
        $this->app->singleton(SettingRepositoryInterface::class, function ($app) {
            return new EloquentSettingRepository(app(\App\Models\Setting::class));
        });
        $this->app->singleton(RoleRepositoryInterface::class, function ($app) {
            return new EloquentRoleRepository(app(\App\Models\Role::class));
        });

        $this->app->singleton(ModuleRepositoryInterface::class, function ($app) {
            return new EloquentModuleRepository(app(\App\Models\Module::class));
        });
        
        // Register User Application UseCases
        $this->app->singleton(LoginUserUseCase::class, function ($app) {
            return new LoginUserUseCase($app->make(AuthenticationService::class));
        });
        
        $this->app->singleton(RegisterUserUseCase::class, function ($app) {
            return new RegisterUserUseCase($app->make(UserService::class));
        });
        
        $this->app->singleton(RegisterAction::class, function ($app) {
            return new RegisterAction($app->make(RegisterUserUseCase::class));
        });
        
        $this->app->singleton(LogoutUserUseCase::class, function ($app) {
            return new LogoutUserUseCase($app->make(AuthenticationService::class));
        });
        
        $this->app->singleton(LoginAction::class, function ($app) {
            return new LoginAction($app->make(LoginUserUseCase::class));
        });
        
        $this->app->singleton(LogoutAction::class, function ($app) {
            return new LogoutAction($app->make(LogoutUserUseCase::class));
        });
        
        $this->app->singleton(ForgotPasswordAction::class, function ($app) {
            return new ForgotPasswordAction($app->make(AuthenticationService::class));
        });
        
        $this->app->singleton(ResetPasswordAction::class, function ($app) {
            return new ResetPasswordAction($app->make(AuthenticationService::class));
        });

        // Register Settings Application Actions
        $this->app->singleton(UpdateSettingAction::class, function ($app) {
            return new UpdateSettingAction($app->make(SettingService::class));
        });

        $this->app->singleton(UpdateMultipleSettingsAction::class, function ($app) {
            return new UpdateMultipleSettingsAction($app->make(SettingService::class));
        });

        $this->app->singleton(GetSettingsByGroupAction::class, function ($app) {
            return new GetSettingsByGroupAction($app->make(SettingService::class));
        });
        
        // Module Application Actions
        $this->app->singleton(CreateModuleAction::class, function ($app) {
            return new CreateModuleAction($app->make(ModuleService::class));
        });

        $this->app->singleton(UpdateModuleAction::class, function ($app) {
            return new UpdateModuleAction($app->make(ModuleService::class));
        });

        $this->app->singleton(DeleteModuleAction::class, function ($app) {
            return new DeleteModuleAction($app->make(ModuleService::class));
        });

        $this->app->singleton(GetModuleAction::class, function ($app) {
            return new GetModuleAction($app->make(ModuleService::class));
        });

        // Register Role Application Actions
        $this->app->singleton(CreateRoleAction::class, function ($app) {
            return new CreateRoleAction($app->make(RoleService::class));
        });

        $this->app->singleton(UpdateRoleAction::class, function ($app) {
            return new UpdateRoleAction($app->make(RoleService::class));
        });
    }

    public function boot(): void
    {
        //
    }
}
