<?php

declare(strict_types=1);

namespace System\Filament\Providers;

use App\Admin\Pages\Settings\GeneralSettingsPage;
use App\Admin\Pages\Settings\ModulesSettingsPage;
use App\Admin\Pages\Settings\RegistrationSettingsPage;
use App\Admin\Pages\Settings\UnitsSettingsPage;
use App\Admin\Resources\ContinentResource;
use App\Admin\Resources\CountryResource;
use App\Admin\Resources\MetroAreaResource;
use App\Admin\Resources\UserResource;
use Filament\Actions\View\ActionsIconAlias;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Infolists\View\InfolistsIconAlias;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Notifications\View\NotificationsIconAlias;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Schemas\View\SchemaIconAlias;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\Support\View\SupportIconAlias;
use Filament\Tables\View\TablesIconAlias;
use Filament\View\PanelsIconAlias;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use System\Settings\ModulesSettings;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            // Initialization
            ->default()
            ->id('admin')
            ->path('admin')
            ->brandName('VAOP Administration')

            // Appearance
            ->colors([
                'primary' => Color::hex('#0068a1'),
                'danger' => Color::Rose,
                'gray' => Color::Zinc,
                'info' => Color::Blue,
                'success' => Color::Emerald,
                'warning' => Color::Yellow,
            ])
            ->icons([
                ActionsIconAlias::DELETE_ACTION_MODAL => 'fas-trash-can',
                ActionsIconAlias::DETACH_ACTION_MODAL => 'fas-xmark',
                ActionsIconAlias::DISSOCIATE_ACTION_MODAL => 'fas-xmark',
                ActionsIconAlias::FORCE_DELETE_ACTION_MODAL => 'fas-trash-can',
                ActionsIconAlias::RESTORE_ACTION_MODAL => 'fas-trash-arrow-up',
                InfolistsIconAlias::COMPONENTS_ICON_ENTRY_FALSE => 'fas-circle-xmark',
                InfolistsIconAlias::COMPONENTS_ICON_ENTRY_TRUE => 'fas-circle-check',
                NotificationsIconAlias::DATABASE_MODAL_EMPTY_STATE => 'fas-bell-slash',
                PanelsIconAlias::PAGES_DASHBOARD_NAVIGATION_ITEM => 'fas-house',
                PanelsIconAlias::RESOURCES_PAGES_EDIT_RECORD_NAVIGATION_ITEM => 'fas-pen-to-square',
                PanelsIconAlias::RESOURCES_PAGES_MANAGE_RELATED_RECORDS_NAVIGATION_ITEM => 'fas-layer-group',
                PanelsIconAlias::RESOURCES_PAGES_VIEW_RECORD_NAVIGATION_ITEM => 'fas-eye',
                PanelsIconAlias::SIDEBAR_COLLAPSE_BUTTON => 'fas-chevron-left',
                PanelsIconAlias::SIDEBAR_COLLAPSE_BUTTON_RTL => 'fas-chevron-right',
                PanelsIconAlias::SIDEBAR_EXPAND_BUTTON => 'fas-chevron-right',
                PanelsIconAlias::SIDEBAR_EXPAND_BUTTON_RTL => 'fas-chevron-left',
                PanelsIconAlias::TOPBAR_CLOSE_SIDEBAR_BUTTON => 'fas-xmark',
                PanelsIconAlias::TOPBAR_OPEN_DATABASE_NOTIFICATIONS_BUTTON => 'fas-bell',
                PanelsIconAlias::TOPBAR_OPEN_SIDEBAR_BUTTON => 'fas-bars',
                SchemaIconAlias::COMPONENTS_WIZARD_COMPLETED_STEP => 'fas-circle-check',
                SupportIconAlias::BREADCRUMBS_SEPARATOR => new HtmlString('/'),
                SupportIconAlias::BREADCRUMBS_SEPARATOR_RTL => new HtmlString('\\'),
                SupportIconAlias::MODAL_CLOSE_BUTTON => 'fas-xmark',
                TablesIconAlias::COLUMNS_ICON_COLUMN_FALSE => 'fas-circle-xmark',
                TablesIconAlias::COLUMNS_ICON_COLUMN_TRUE => 'fas-circle-check',
                TablesIconAlias::EMPTY_STATE => 'fas-xmark',
            ])
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->maxContentWidth(Width::ScreenTwoExtraLarge)

            // Render Hooks
            ->renderHook(
                PanelsRenderHook::SIDEBAR_FOOTER,
                fn () => new HtmlString(
                    '<div class="fi-sidebar-footer">'.
                        '<span>v'.config('vaop.version').'</span>'.
                    '</div>'
                ),
            )

            // Navigation
            ->navigation(function (NavigationBuilder $builder): NavigationBuilder {
                return $builder
                    ->items([
                        NavigationItem::make()
                            ->label('Dashboard')
                            ->icon('fas-house')
                            ->isActiveWhen(fn (): bool => request()->routeIs(Dashboard::getRouteName()))
                            ->url(fn (): string => Dashboard::getUrl()),
                    ])
                    ->groups([
                        NavigationGroup::make('Scheduling')
                            ->items([
                                ...(app(ModulesSettings::class)->enableMetroAreas ? MetroAreaResource::getNavigationItems() : []),
                            ]),
                        NavigationGroup::make('Pilots')
                            ->items([
                                ...UserResource::getNavigationItems(),
                            ]),
                        NavigationGroup::make('Reference Data')
                            ->collapsible()
                            ->collapsed()
                            ->items([
                                ...ContinentResource::getNavigationItems(),
                                ...CountryResource::getNavigationItems(),
                            ]),
                        NavigationGroup::make('Settings')
                            ->collapsible()
                            ->items([
                                ...GeneralSettingsPage::getNavigationItems(),
                                ...RegistrationSettingsPage::getNavigationItems(),
                                ...UnitsSettingsPage::getNavigationItems(),
                                ...ModulesSettingsPage::getNavigationItems(),
                            ]),
                    ]);
            })

            // Discovery
            ->discoverResources(in: base_path('src/App/Admin/Resources'), for: 'App\Admin\Resources')
            ->discoverPages(in: base_path('src/App/Admin/Pages'), for: 'App\Admin\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: base_path('src/App/Admin/Widgets'), for: 'App\Admin\Widgets')
            ->widgets([])

            // Middleware
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
