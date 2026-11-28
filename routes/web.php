<?php

use App\Http\Controllers\Accadmin\BackupController as AccadminBackupController;
use App\Http\Controllers\Accadmin\DashboardController as AccadminDashboardController;
use App\Http\Controllers\Accadmin\OwnerController as AccadminOwnerController;
use App\Http\Controllers\AssistanceController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BookingWorkflowController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\Guest\BookingController as GuestBookingController;
use App\Http\Controllers\Guest\DashboardController as GuestDashboardController;
use App\Http\Controllers\Guest\FeedbackController as GuestFeedbackController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Owner\AdminController as OwnerAdminController;
use App\Http\Controllers\Owner\DashboardController as OwnerDashboardController;
use App\Http\Controllers\Owner\HouseController as OwnerHouseController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\PublicVisitorConversationController;
use App\Http\Controllers\Staff\DashboardController as StaffDashboardController;
use App\Livewire\Guest\BookingForm;
use App\Livewire\Owner\WalkInBookingForm;
use Illuminate\Support\Facades\Route;

// Public browsing — no auth required
Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/transients', [PublicController::class, 'transients'])->name('public.transients');
Route::get('/houses/{house}', [PublicController::class, 'house'])->name('public.house');
Route::post('/houses/{house}/inquiries', [PublicController::class, 'sendHouseInquiry'])->middleware('throttle:5,1')->name('public.houses.inquiries.store');
Route::get('/units/{unit}', [PublicController::class, 'unit'])->name('public.unit');
Route::post('/units/{unit}/questions', [PublicController::class, 'unitQuestion'])
    ->middleware('throttle:10,1')
    ->name('public.units.questions.store');
Route::post('/units/{unit}/inquiries', [PublicController::class, 'sendInquiry'])->middleware('throttle:5,1')->name('public.units.inquiries.store');
Route::get('/public-chat/context', [PublicVisitorConversationController::class, 'context'])
    ->middleware('throttle:30,1')
    ->name('public.visitor-chat.context');
Route::post('/public-chat/answer', [PublicVisitorConversationController::class, 'answer'])
    ->middleware('throttle:10,1')
    ->name('public.visitor-chat.answer');
Route::get('/public-chat/conversations', [PublicVisitorConversationController::class, 'index'])
    ->name('public.visitor-conversations.index');
Route::post('/public-chat/conversations', [PublicVisitorConversationController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('public.visitor-conversations.store');
Route::get('/public-chat/conversations/{conversationId}/access', [PublicVisitorConversationController::class, 'access'])
    ->whereNumber('conversationId')
    ->middleware('signed')
    ->name('public.visitor-conversations.access');
Route::get('/public-chat/conversations/{conversationId}/sign-in', [PublicVisitorConversationController::class, 'signIn'])
    ->whereNumber('conversationId')
    ->name('public.visitor-conversations.sign-in');
Route::get('/public-chat/conversations/{conversationId}', [PublicVisitorConversationController::class, 'show'])
    ->whereNumber('conversationId')
    ->name('public.visitor-conversations.show');
Route::get('/public-chat/conversations/{conversationId}/updates', [PublicVisitorConversationController::class, 'updates'])
    ->whereNumber('conversationId')
    ->middleware('throttle:60,1')
    ->name('public.visitor-conversations.updates');
Route::post('/public-chat/conversations/{conversationId}/reply', [PublicVisitorConversationController::class, 'reply'])
    ->whereNumber('conversationId')
    ->middleware('throttle:10,1')
    ->name('public.visitor-conversations.reply');
Route::post('/public-chat/conversations/{conversationId}/archive', [PublicVisitorConversationController::class, 'archive'])
    ->whereNumber('conversationId')
    ->name('public.visitor-conversations.archive');
Route::post('/public-chat/conversations/{conversationId}/delete', [PublicVisitorConversationController::class, 'deleteConversation'])
    ->whereNumber('conversationId')
    ->name('public.visitor-conversations.delete');
Route::post('/public-chat/conversations/{conversationId}/restore', [PublicVisitorConversationController::class, 'restore'])
    ->whereNumber('conversationId')
    ->name('public.visitor-conversations.restore');
Route::get('/guest/feedback/{booking}/{token}', [GuestFeedbackController::class, 'show'])
    ->name('guest.feedback.show');
Route::post('/guest/feedback/{booking}/{token}', [GuestFeedbackController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('guest.feedback.store');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
    Route::get('/forgot-password', [PasswordResetController::class, 'requestForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])
        ->middleware('throttle:5,1')
        ->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'resetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])
        ->middleware('throttle:10,1')
        ->name('password.update');
});

Route::get('/register', [RegisterController::class, 'create'])->name('register');
Route::post('/register', [RegisterController::class, 'store'])->name('register.store');

Route::get('/logout', fn () => redirect()->route('home'));

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/security', [ProfileController::class, 'security'])->name('profile.security');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::post('/profile/deactivate', [ProfileController::class, 'deactivate'])->name('profile.deactivate');
    Route::get('/notifications/{notificationId}', [NotificationController::class, 'open'])->name('notifications.open');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::get('/payments/{paymentId}/proof', [BookingWorkflowController::class, 'downloadProof'])->name('payments.proof');
});

Route::middleware(['auth', 'role:guest'])->group(function () {
    Route::get('/guest/dashboard', [GuestDashboardController::class, 'index'])->name('guest.dashboard');
    Route::get('/guest/conversations', [ConversationController::class, 'guestIndex'])->name('guest.conversations.index');
    Route::post('/guest/conversations', [ConversationController::class, 'guestStore'])->name('guest.conversations.store');
    Route::get('/guest/conversations/{conversationId}', [ConversationController::class, 'guestShow'])
        ->whereNumber('conversationId')
        ->name('guest.conversations.show');
    Route::get('/guest/conversations/{conversationId}/updates', [ConversationController::class, 'updates'])
        ->whereNumber('conversationId')
        ->middleware('throttle:60,1')
        ->name('guest.conversations.updates');
    Route::post('/guest/conversations/{conversationId}/reply', [ConversationController::class, 'guestReply'])
        ->whereNumber('conversationId')
        ->name('guest.conversations.reply');
    Route::post('/guest/conversations/{conversationId}/archive', [ConversationController::class, 'archive'])
        ->whereNumber('conversationId')
        ->name('guest.conversations.archive');
    Route::post('/guest/conversations/{conversationId}/delete', [ConversationController::class, 'deleteConversation'])
        ->whereNumber('conversationId')
        ->name('guest.conversations.delete');
    Route::post('/guest/conversations/{conversationId}/restore', [ConversationController::class, 'restore'])
        ->whereNumber('conversationId')
        ->name('guest.conversations.restore');
    Route::get('/units/{unit}/book', BookingForm::class)->name('guest.book');

    Route::get('/guest/bookings/{booking_id}', [GuestBookingController::class, 'show'])->name('guest.bookings.show');
    Route::get('/guest/bookings/{booking_id}/ticket', [GuestBookingController::class, 'ticket'])->name('guest.bookings.ticket');
    Route::post('/guest/bookings/{booking_id}/ticket/email', [GuestBookingController::class, 'resendTicket'])->name('guest.bookings.ticket.email');
    Route::get('/guest/bookings/{booking_id}/payment', [GuestBookingController::class, 'payment'])->name('guest.bookings.payment');
    Route::post('/guest/bookings/{booking_id}/payments', [GuestBookingController::class, 'submitPayment'])->name('guest.bookings.payments.store');
    Route::post('/guest/bookings/{booking_id}/cancel', [GuestBookingController::class, 'cancel'])->name('guest.bookings.cancel');
    Route::get('/guest/bookings/{booking_id}/receipt', [GuestBookingController::class, 'receipt'])->name('guest.bookings.receipt');
    Route::get('/guest/bookings/{booking_id}/receipt/pdf', [GuestBookingController::class, 'receiptPdf'])->name('guest.bookings.receipt.pdf');
});

Route::middleware(['auth', 'role:accadmin'])->prefix('accadmin')->name('accadmin.')->group(function () {
    Route::get('/dashboard', [AccadminDashboardController::class, 'index'])->name('dashboard');
    Route::post('/backups', [AccadminBackupController::class, 'store'])->name('backups.store');
    Route::get('/backups/{filename}', [AccadminBackupController::class, 'download'])->name('backups.download');
    Route::get('/owners', [AccadminOwnerController::class, 'index'])->name('owners.index');
    Route::get('/owners/deactivated', [AccadminOwnerController::class, 'deactivated'])->name('owners.deactivated');
    Route::post('/owners/invite', [AccadminOwnerController::class, 'invite'])->name('owners.invite');
    Route::post('/owners/invitations/{invitation}/resend', [AccadminOwnerController::class, 'resendInvitation'])->name('owners.invitations.resend');
    Route::post('/owners/deactivate-selected', [AccadminOwnerController::class, 'bulkDeactivate'])->name('owners.bulk-deactivate');
    Route::post('/owners/reactivate-selected', [AccadminOwnerController::class, 'bulkReactivate'])->name('owners.bulk-reactivate');
    Route::post('/owners/{ownerId}/deactivate', [AccadminOwnerController::class, 'deactivate'])->whereNumber('ownerId')->name('owners.deactivate');
    Route::post('/owners/{ownerId}/reactivate', [AccadminOwnerController::class, 'reactivate'])->whereNumber('ownerId')->name('owners.reactivate');
    Route::delete('/owners', [AccadminOwnerController::class, 'bulkDestroy'])->name('owners.bulk-destroy');
    Route::delete('/owners/{ownerId}', [AccadminOwnerController::class, 'destroy'])->whereNumber('ownerId')->name('owners.destroy');
});

Route::middleware(['auth', 'role:owner'])->prefix('owner')->name('owner.')->group(function () {
    Route::get('/dashboard', [OwnerDashboardController::class, 'index'])->name('dashboard');
    Route::get('/messages', [ConversationController::class, 'staffIndex'])->name('conversations.index');
    Route::get('/messages/new', [ConversationController::class, 'staffCreate'])->name('conversations.create');
    Route::post('/messages/new', [ConversationController::class, 'staffCreateStore'])->name('conversations.create.store');
    Route::get('/messages/{conversationId}', [ConversationController::class, 'staffShow'])->whereNumber('conversationId')->name('conversations.show');
    Route::get('/messages/{conversationId}/updates', [ConversationController::class, 'updates'])
        ->whereNumber('conversationId')
        ->middleware('throttle:60,1')
        ->name('conversations.updates');
    Route::post('/messages/{conversationId}', [ConversationController::class, 'staffStore'])->whereNumber('conversationId')->name('conversations.store');
    Route::post('/messages/{conversationId}/archive', [ConversationController::class, 'archive'])->whereNumber('conversationId')->name('conversations.archive');
    Route::post('/messages/{conversationId}/delete', [ConversationController::class, 'deleteConversation'])->whereNumber('conversationId')->name('conversations.delete');
    Route::post('/messages/{conversationId}/restore', [ConversationController::class, 'restore'])->whereNumber('conversationId')->name('conversations.restore');
    Route::post('/messages/{conversationId}/notes', [ConversationController::class, 'noteStore'])->whereNumber('conversationId')->name('conversations.notes.store');
    Route::get('/messages/assistance', [AssistanceController::class, 'index'])->name('assistance.index');
    Route::post('/messages/assistance/settings', [AssistanceController::class, 'updateSettings'])->name('assistance.settings.update');
    Route::post('/messages/assistance/faqs', [AssistanceController::class, 'storeFaq'])->name('assistance.faqs.store');
    Route::put('/messages/assistance/faqs/{faq}', [AssistanceController::class, 'updateFaq'])->name('assistance.faqs.update');
    Route::post('/messages/assistance/quick-replies', [AssistanceController::class, 'storeQuickReply'])->name('assistance.quick-replies.store');
    Route::put('/messages/assistance/quick-replies/{quickReply}', [AssistanceController::class, 'updateQuickReply'])->name('assistance.quick-replies.update');
    Route::get('/houses', [OwnerHouseController::class, 'index'])->name('houses.index');
    Route::get('/houses/archive', [OwnerHouseController::class, 'archiveIndex'])->name('houses.archive.index');
    Route::get('/houses/archive/export', [OwnerHouseController::class, 'exportArchive'])->name('houses.archive.export');
    Route::post('/houses/archive/bulk', [OwnerHouseController::class, 'bulkArchiveAction'])->name('houses.archive.bulk');
    Route::post('/houses/archive/bulk-houses', [OwnerHouseController::class, 'bulkHouseArchiveAction'])->name('houses.archive.houses');
    Route::post('/houses/archive/bulk-units', [OwnerHouseController::class, 'bulkUnitArchiveAction'])->name('houses.archive.units');
    Route::get('/houses/create', [OwnerHouseController::class, 'create'])->name('houses.create');
    Route::post('/houses', [OwnerHouseController::class, 'store'])->name('houses.store');
    Route::post('/houses/{house}/archive', [OwnerHouseController::class, 'archiveHouse'])->name('houses.archive');
    Route::post('/houses/{house}/restore', [OwnerHouseController::class, 'restoreHouse'])->name('houses.restore');
    Route::delete('/houses/{house}/permanently', [OwnerHouseController::class, 'permanentlyDeleteHouse'])->name('houses.permanently-delete');
    Route::get('/houses/{house}/edit', [OwnerHouseController::class, 'edit'])->name('houses.edit');
    Route::put('/houses/{house}', [OwnerHouseController::class, 'update'])->name('houses.update');
    Route::put('/houses/{house}/availability', [OwnerHouseController::class, 'updateHouseAvailability'])->name('houses.availability');
    Route::get('/houses/{house}/units', [OwnerHouseController::class, 'units'])->name('houses.units');
    Route::get('/houses/{house}/units/create', [OwnerHouseController::class, 'createUnit'])->name('houses.units.create');
    Route::post('/houses/{house}/units', [OwnerHouseController::class, 'storeUnit'])->name('houses.units.store');
    Route::post('/houses/{house}/units/{unit}/archive', [OwnerHouseController::class, 'archiveUnit'])->name('houses.units.archive');
    Route::post('/houses/{house}/units/{unit}/restore', [OwnerHouseController::class, 'restoreUnit'])->name('houses.units.restore');
    Route::delete('/houses/{house}/units/{unit}/permanently', [OwnerHouseController::class, 'permanentlyDeleteUnit'])->name('houses.units.permanently-delete');
    Route::get('/houses/{house}/units/{unit}/edit', [OwnerHouseController::class, 'editUnit'])->name('houses.units.edit');
    Route::put('/houses/{house}/units/{unit}', [OwnerHouseController::class, 'updateUnit'])->name('houses.units.update');
    Route::put('/houses/{house}/units/{unit}/availability', [OwnerHouseController::class, 'updateUnitAvailability'])->name('houses.units.availability');
    Route::get('/admins', [OwnerAdminController::class, 'index'])->name('admins.index');
    Route::get('/admins/deactivated', [OwnerAdminController::class, 'deactivated'])->name('admins.deactivated');
    Route::post('/admins/invite', [OwnerAdminController::class, 'invite'])->name('admins.invite');
    Route::post('/admins/invitations/{invitation}/resend', [OwnerAdminController::class, 'resendInvitation'])->name('admins.invitations.resend');
    Route::post('/admins/deactivate-selected', [OwnerAdminController::class, 'bulkDeactivate'])->name('admins.bulk-deactivate');
    Route::post('/admins/reactivate-selected', [OwnerAdminController::class, 'bulkReactivate'])->name('admins.bulk-reactivate');
    Route::post('/admins/delete-selected', [OwnerAdminController::class, 'bulkPermanentlyDelete'])->name('admins.bulk-permanent-delete');
    Route::post('/admins/{invitation}/deactivate', [OwnerAdminController::class, 'deactivate'])->name('admins.deactivate');
    Route::post('/admins/{invitation}/reactivate', [OwnerAdminController::class, 'reactivate'])->name('admins.reactivate');
    Route::delete('/admins/{invitation}/permanently', [OwnerAdminController::class, 'permanentlyDelete'])->name('admins.permanent-delete');
    Route::delete('/admins/{invitation}', [OwnerAdminController::class, 'remove'])->name('admins.remove');
    Route::get('/reports/excel', [BookingWorkflowController::class, 'downloadExcel'])->name('reports.excel');
    Route::get('/reports/pdf', [BookingWorkflowController::class, 'downloadPdf'])->name('reports.pdf');
    Route::get('/bookings', [BookingWorkflowController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/create', WalkInBookingForm::class)->name('bookings.create');
    Route::get('/bookings/{bookingId}', [BookingWorkflowController::class, 'show'])->name('bookings.show');
    Route::post('/bookings/{bookingId}/accept', [BookingWorkflowController::class, 'accept'])->name('bookings.accept');
    Route::post('/bookings/{bookingId}/reject', [BookingWorkflowController::class, 'reject'])->name('bookings.reject');
    Route::post('/bookings/{bookingId}/cancel', [BookingWorkflowController::class, 'cancel'])->name('bookings.cancel');
    Route::post('/bookings/{bookingId}/refunds', [BookingWorkflowController::class, 'refundBooking'])->name('bookings.refunds.store');
    Route::post('/bookings/calendar/blocks', [BookingWorkflowController::class, 'storeCalendarBlock'])->name('bookings.calendar-blocks.store');
    Route::delete('/bookings/calendar/blocks/{availabilityId}', [BookingWorkflowController::class, 'destroyCalendarBlock'])->name('bookings.calendar-blocks.destroy');
    Route::get('/bookings/{bookingId}/edit', [BookingWorkflowController::class, 'edit'])->name('bookings.edit');
    Route::put('/bookings/{bookingId}', [BookingWorkflowController::class, 'update'])->name('bookings.update');
    Route::get('/bookings/{bookingId}/checkout', [BookingWorkflowController::class, 'checkout'])->name('bookings.checkout');
    Route::post('/bookings/{bookingId}/check-in', [BookingWorkflowController::class, 'checkIn'])->name('bookings.check-in');
    Route::get('/guests/{bookingId}/check-in', [BookingWorkflowController::class, 'guestCheckIn'])->name('guests.check-in');
    Route::post('/guests/{bookingId}/headcount', [BookingWorkflowController::class, 'updateCheckInHeadcount'])->name('guests.headcount.update');
    Route::post('/guests/{bookingId}/check-in/{bookingGuestId}/arrival', [BookingWorkflowController::class, 'recordGuestArrival'])->name('guests.arrivals.store');
    Route::post('/guests/{bookingId}/check-in/{bookingGuestId}/no-show', [BookingWorkflowController::class, 'markGuestNoShow'])->name('guests.no-shows.store');
    Route::post('/guests/{bookingId}/check-in/{bookingGuestId}/replacement', [BookingWorkflowController::class, 'replaceGuest'])->name('guests.replacements.store');
    Route::post('/guests/{bookingId}/check-in/{bookingGuestId}/identity', [BookingWorkflowController::class, 'reviewGuestIdentity'])->name('guests.identity.review');
    Route::post('/guests/{bookingId}/refunds', [BookingWorkflowController::class, 'refundCheckedInGuest'])->name('guests.refunds.store');
    Route::post('/guests/{bookingId}/amenities', [BookingWorkflowController::class, 'addBookingAddons'])->name('guests.addons.store');
    Route::post('/guests/{bookingId}/amenities/{addonId}/quantity', [BookingWorkflowController::class, 'adjustBookingAddonQuantity'])->whereNumber('addonId')->name('guests.addons.quantity');
    Route::post('/guests/{bookingId}/feedback', [BookingWorkflowController::class, 'sendFeedbackRequest'])->name('guests.feedback.send');
    Route::post('/bookings/{bookingId}/checkout/charges', [BookingWorkflowController::class, 'addCheckoutCharge'])->name('bookings.checkout.charges');
    Route::post('/bookings/{bookingId}/checkout/charges/{chargeId}/quantity', [BookingWorkflowController::class, 'adjustCheckoutChargeQuantity'])->whereNumber('chargeId')->name('bookings.checkout.charges.quantity');
    Route::post('/bookings/{bookingId}/checkout/damages', [BookingWorkflowController::class, 'addCheckoutDamages'])->name('bookings.checkout.damages.store');
    Route::post('/bookings/{bookingId}/checkout/damages/custom', [BookingWorkflowController::class, 'addCustomCheckoutDamage'])->name('bookings.checkout.damages.custom');
    Route::post('/bookings/{bookingId}/checkout/damages/{damageId}/quantity', [BookingWorkflowController::class, 'adjustCheckoutDamageQuantity'])->whereNumber('damageId')->name('bookings.checkout.damages.quantity');
    Route::post('/bookings/{bookingId}/checkout/payments', [BookingWorkflowController::class, 'recordCheckoutPayment'])->name('bookings.checkout.payments');
    Route::post('/bookings/{bookingId}/checkout/refunds/{refundId}', [BookingWorkflowController::class, 'processCheckoutRefund'])->whereNumber('refundId')->name('bookings.checkout.refunds.process');
    Route::post('/bookings/{bookingId}/checkout/complete', [BookingWorkflowController::class, 'completeCheckout'])->name('bookings.checkout.complete');
    Route::post('/bookings/{bookingId}/archive', [BookingWorkflowController::class, 'archive'])->name('bookings.archive');
    Route::post('/bookings/archive-selected', [BookingWorkflowController::class, 'archiveSelected'])->name('bookings.archive-selected');
    Route::post('/discounts/{discountId}/review', [BookingWorkflowController::class, 'reviewDiscount'])->name('discounts.review');
    Route::post('/payments/{paymentId}/review', [BookingWorkflowController::class, 'reviewPayment'])->name('payments.review');
});

Route::middleware(['auth', 'role:staff'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/dashboard', [StaffDashboardController::class, 'index'])->name('dashboard');
    Route::get('/messages', [ConversationController::class, 'staffIndex'])->name('conversations.index');
    Route::get('/messages/new', [ConversationController::class, 'staffCreate'])->name('conversations.create');
    Route::post('/messages/new', [ConversationController::class, 'staffCreateStore'])->name('conversations.create.store');
    Route::get('/messages/{conversationId}', [ConversationController::class, 'staffShow'])->whereNumber('conversationId')->name('conversations.show');
    Route::get('/messages/{conversationId}/updates', [ConversationController::class, 'updates'])
        ->whereNumber('conversationId')
        ->middleware('throttle:60,1')
        ->name('conversations.updates');
    Route::post('/messages/{conversationId}', [ConversationController::class, 'staffStore'])->whereNumber('conversationId')->name('conversations.store');
    Route::post('/messages/{conversationId}/archive', [ConversationController::class, 'archive'])->whereNumber('conversationId')->name('conversations.archive');
    Route::post('/messages/{conversationId}/delete', [ConversationController::class, 'deleteConversation'])->whereNumber('conversationId')->name('conversations.delete');
    Route::post('/messages/{conversationId}/restore', [ConversationController::class, 'restore'])->whereNumber('conversationId')->name('conversations.restore');
    Route::post('/messages/{conversationId}/notes', [ConversationController::class, 'noteStore'])->whereNumber('conversationId')->name('conversations.notes.store');
    Route::get('/messages/assistance', [AssistanceController::class, 'index'])->name('assistance.index');
    Route::post('/messages/assistance/settings', [AssistanceController::class, 'updateSettings'])->name('assistance.settings.update');
    Route::post('/messages/assistance/faqs', [AssistanceController::class, 'storeFaq'])->name('assistance.faqs.store');
    Route::put('/messages/assistance/faqs/{faq}', [AssistanceController::class, 'updateFaq'])->name('assistance.faqs.update');
    Route::post('/messages/assistance/quick-replies', [AssistanceController::class, 'storeQuickReply'])->name('assistance.quick-replies.store');
    Route::put('/messages/assistance/quick-replies/{quickReply}', [AssistanceController::class, 'updateQuickReply'])->name('assistance.quick-replies.update');
    Route::get('/houses', [OwnerHouseController::class, 'index'])->name('houses.index');
    Route::get('/houses/archive', [OwnerHouseController::class, 'archiveIndex'])->name('houses.archive.index');
    Route::get('/houses/archive/export', [OwnerHouseController::class, 'exportArchive'])->name('houses.archive.export');
    Route::post('/houses/archive/bulk', [OwnerHouseController::class, 'bulkArchiveAction'])->name('houses.archive.bulk');
    Route::post('/houses/archive/bulk-houses', [OwnerHouseController::class, 'bulkHouseArchiveAction'])->name('houses.archive.houses');
    Route::post('/houses/archive/bulk-units', [OwnerHouseController::class, 'bulkUnitArchiveAction'])->name('houses.archive.units');
    Route::post('/houses/{house}/archive', [OwnerHouseController::class, 'archiveHouse'])->name('houses.archive');
    Route::post('/houses/{house}/restore', [OwnerHouseController::class, 'restoreHouse'])->name('houses.restore');
    Route::delete('/houses/{house}/permanently', [OwnerHouseController::class, 'permanentlyDeleteHouse'])->name('houses.permanently-delete');
    Route::get('/houses/{house}/edit', [OwnerHouseController::class, 'edit'])->name('houses.edit');
    Route::put('/houses/{house}', [OwnerHouseController::class, 'update'])->name('houses.update');
    Route::put('/houses/{house}/availability', [OwnerHouseController::class, 'updateHouseAvailability'])->name('houses.availability');
    Route::get('/houses/{house}/units', [OwnerHouseController::class, 'units'])->name('houses.units');
    Route::get('/houses/{house}/units/create', [OwnerHouseController::class, 'createUnit'])->name('houses.units.create');
    Route::post('/houses/{house}/units', [OwnerHouseController::class, 'storeUnit'])->name('houses.units.store');
    Route::post('/houses/{house}/units/{unit}/archive', [OwnerHouseController::class, 'archiveUnit'])->name('houses.units.archive');
    Route::post('/houses/{house}/units/{unit}/restore', [OwnerHouseController::class, 'restoreUnit'])->name('houses.units.restore');
    Route::delete('/houses/{house}/units/{unit}/permanently', [OwnerHouseController::class, 'permanentlyDeleteUnit'])->name('houses.units.permanently-delete');
    Route::get('/houses/{house}/units/{unit}/edit', [OwnerHouseController::class, 'editUnit'])->name('houses.units.edit');
    Route::put('/houses/{house}/units/{unit}', [OwnerHouseController::class, 'updateUnit'])->name('houses.units.update');
    Route::put('/houses/{house}/units/{unit}/availability', [OwnerHouseController::class, 'updateUnitAvailability'])->name('houses.units.availability');
    Route::get('/reports/excel', [BookingWorkflowController::class, 'downloadExcel'])->name('reports.excel');
    Route::get('/reports/pdf', [BookingWorkflowController::class, 'downloadPdf'])->name('reports.pdf');
    Route::get('/bookings', [BookingWorkflowController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/create', WalkInBookingForm::class)->name('bookings.create');
    Route::get('/bookings/{bookingId}', [BookingWorkflowController::class, 'show'])->name('bookings.show');
    Route::post('/bookings/{bookingId}/accept', [BookingWorkflowController::class, 'accept'])->name('bookings.accept');
    Route::post('/bookings/{bookingId}/reject', [BookingWorkflowController::class, 'reject'])->name('bookings.reject');
    Route::post('/bookings/{bookingId}/cancel', [BookingWorkflowController::class, 'cancel'])->name('bookings.cancel');
    Route::post('/bookings/{bookingId}/refunds', [BookingWorkflowController::class, 'refundBooking'])->name('bookings.refunds.store');
    Route::post('/bookings/calendar/blocks', [BookingWorkflowController::class, 'storeCalendarBlock'])->name('bookings.calendar-blocks.store');
    Route::delete('/bookings/calendar/blocks/{availabilityId}', [BookingWorkflowController::class, 'destroyCalendarBlock'])->name('bookings.calendar-blocks.destroy');
    Route::get('/bookings/{bookingId}/edit', [BookingWorkflowController::class, 'edit'])->name('bookings.edit');
    Route::put('/bookings/{bookingId}', [BookingWorkflowController::class, 'update'])->name('bookings.update');
    Route::get('/bookings/{bookingId}/checkout', [BookingWorkflowController::class, 'checkout'])->name('bookings.checkout');
    Route::post('/bookings/{bookingId}/check-in', [BookingWorkflowController::class, 'checkIn'])->name('bookings.check-in');
    Route::get('/guests/{bookingId}/check-in', [BookingWorkflowController::class, 'guestCheckIn'])->name('guests.check-in');
    Route::post('/guests/{bookingId}/headcount', [BookingWorkflowController::class, 'updateCheckInHeadcount'])->name('guests.headcount.update');
    Route::post('/guests/{bookingId}/check-in/{bookingGuestId}/arrival', [BookingWorkflowController::class, 'recordGuestArrival'])->name('guests.arrivals.store');
    Route::post('/guests/{bookingId}/check-in/{bookingGuestId}/no-show', [BookingWorkflowController::class, 'markGuestNoShow'])->name('guests.no-shows.store');
    Route::post('/guests/{bookingId}/check-in/{bookingGuestId}/replacement', [BookingWorkflowController::class, 'replaceGuest'])->name('guests.replacements.store');
    Route::post('/guests/{bookingId}/check-in/{bookingGuestId}/identity', [BookingWorkflowController::class, 'reviewGuestIdentity'])->name('guests.identity.review');
    Route::post('/guests/{bookingId}/refunds', [BookingWorkflowController::class, 'refundCheckedInGuest'])->name('guests.refunds.store');
    Route::post('/guests/{bookingId}/amenities', [BookingWorkflowController::class, 'addBookingAddons'])->name('guests.addons.store');
    Route::post('/guests/{bookingId}/amenities/{addonId}/quantity', [BookingWorkflowController::class, 'adjustBookingAddonQuantity'])->whereNumber('addonId')->name('guests.addons.quantity');
    Route::post('/guests/{bookingId}/feedback', [BookingWorkflowController::class, 'sendFeedbackRequest'])->name('guests.feedback.send');
    Route::post('/bookings/{bookingId}/checkout/charges', [BookingWorkflowController::class, 'addCheckoutCharge'])->name('bookings.checkout.charges');
    Route::post('/bookings/{bookingId}/checkout/charges/{chargeId}/quantity', [BookingWorkflowController::class, 'adjustCheckoutChargeQuantity'])->whereNumber('chargeId')->name('bookings.checkout.charges.quantity');
    Route::post('/bookings/{bookingId}/checkout/damages', [BookingWorkflowController::class, 'addCheckoutDamages'])->name('bookings.checkout.damages.store');
    Route::post('/bookings/{bookingId}/checkout/damages/custom', [BookingWorkflowController::class, 'addCustomCheckoutDamage'])->name('bookings.checkout.damages.custom');
    Route::post('/bookings/{bookingId}/checkout/damages/{damageId}/quantity', [BookingWorkflowController::class, 'adjustCheckoutDamageQuantity'])->whereNumber('damageId')->name('bookings.checkout.damages.quantity');
    Route::post('/bookings/{bookingId}/checkout/payments', [BookingWorkflowController::class, 'recordCheckoutPayment'])->name('bookings.checkout.payments');
    Route::post('/bookings/{bookingId}/checkout/refunds/{refundId}', [BookingWorkflowController::class, 'processCheckoutRefund'])->whereNumber('refundId')->name('bookings.checkout.refunds.process');
    Route::post('/bookings/{bookingId}/checkout/complete', [BookingWorkflowController::class, 'completeCheckout'])->name('bookings.checkout.complete');
    Route::post('/bookings/{bookingId}/archive', [BookingWorkflowController::class, 'archive'])->name('bookings.archive');
    Route::post('/bookings/archive-selected', [BookingWorkflowController::class, 'archiveSelected'])->name('bookings.archive-selected');
    Route::post('/discounts/{discountId}/review', [BookingWorkflowController::class, 'reviewDiscount'])->name('discounts.review');
    Route::post('/payments/{paymentId}/review', [BookingWorkflowController::class, 'reviewPayment'])->name('payments.review');
});
