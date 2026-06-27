<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Superadmin\ImportHajjParticipantRequest;
use App\Http\Requests\Superadmin\StoreHajjParticipantRequest;
use App\Http\Requests\Superadmin\UpdateHajjParticipantRequest;
use App\Jobs\ImportHajjParticipantsJob;
use App\Models\HajjParticipant;
use App\Support\Hajj\HajjExcelImportService;
use App\Support\Hajj\HajjExcelTemplateService;
use App\Support\Hajj\HajjImportProgress;
use App\Support\Hajj\HajjParticipantUserProvisioner;
use App\Support\Hajj\HajjTahunOptions;
use App\Support\Wilayah\KemendagriWilayahRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

class HajjParticipantController extends Controller
{
    public function __construct(
        private readonly HajjParticipantUserProvisioner $provisioner,
        private readonly HajjExcelImportService $importService,
        private readonly HajjExcelTemplateService $templateService,
        private readonly KemendagriWilayahRepository $wilayah,
    ) {}

    public function index(Request $request): Response
    {
        $items = HajjParticipant::query()
            ->with('user:id,name,email,is_active')
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = '%'.$request->string('q').'%';
                $query->where(function ($builder) use ($q) {
                    $builder->where('nama', 'like', $q)
                        ->orWhere('nomor_porsi', 'like', $q)
                        ->orWhere('desa', 'like', $q);
                });
            })
            ->when($request->filled('tahun_haji'), fn ($query) => $query->where('tahun_haji', $request->integer('tahun_haji')))
            ->when($request->filled('kecamatan_kode'), function ($query) use ($request) {
                $nama = $this->wilayah->kecamatanNama($request->string('kecamatan_kode')->toString());
                if ($nama) {
                    $query->where('kecamatan', $nama);
                }
            })
            ->when(
                $request->filled('kecamatan_kode') && $request->filled('desa_kode'),
                function ($query) use ($request) {
                    $nama = $this->wilayah->desaNama(
                        $request->string('kecamatan_kode')->toString(),
                        $request->string('desa_kode')->toString(),
                    );
                    if ($nama) {
                        $query->where('desa', $nama);
                    }
                },
            )
            ->orderByDesc('tahun_haji')
            ->orderBy('nama')
            ->paginate($request->integer('per_page', 10))
            ->withQueryString();

        return Inertia::render('Admin/HajjParticipants/Index', [
            'items' => $items,
            'filters' => [
                'q' => $request->string('q')->toString(),
                'tahun_haji' => $request->string('tahun_haji')->toString(),
                'kecamatan_kode' => $request->string('kecamatan_kode')->toString(),
                'desa_kode' => $request->string('desa_kode')->toString(),
            ],
            'tahunOptions' => HajjTahunOptions::values(),
            'wilayah' => $this->wilayah->forFrontend(),
        ]);
    }

    public function store(StoreHajjParticipantRequest $request): RedirectResponse
    {
        $payload = $request->validated();
        $email = $payload['email'] ?? null;
        unset($payload['email']);

        try {
            $result = DB::transaction(function () use ($payload, $email, $request) {
                $participant = HajjParticipant::create($payload + ['created_by' => $request->user()?->id]);

                return $this->provisioner->provision($participant, $email);
            });
        } catch (RuntimeException $e) {
            return back()->withErrors(['role' => $e->getMessage()]);
        }

        return back()->with([
            'status' => 'created',
            'generated_password' => $result['password'],
            'generated_username' => $result['user']->username,
            'generated_email' => $result['user']->email,
        ]);
    }

    public function update(UpdateHajjParticipantRequest $request, HajjParticipant $hajjParticipant): RedirectResponse
    {
        $payload = $request->validated();
        $hajjParticipant->update($payload);

        if ($hajjParticipant->user) {
            $hajjParticipant->user->update(['name' => $payload['nama']]);
        }

        return back()->with('status', 'updated');
    }

    public function destroy(HajjParticipant $hajjParticipant): RedirectResponse
    {
        $user = $hajjParticipant->user;

        $hajjParticipant->delete();

        $user?->delete();

        return back()->with('status', 'deleted');
    }

    public function import(ImportHajjParticipantRequest $request): RedirectResponse
    {
        $payload = $request->validated();
        $storedPath = $request->file('file')->store('temp/hajj-imports');
        $token = Str::uuid()->toString();

        HajjImportProgress::start($token, $request->user()?->id);

        ImportHajjParticipantsJob::dispatch(
            $storedPath,
            (int) $payload['tahun_haji'],
            $request->user()?->id,
            $token,
            $payload['duplicate_action'] ?? 'skip',
        );

        return back()->with('import_job_token', $token);
    }

    public function importStatus(Request $request, string $token): JsonResponse
    {
        $status = HajjImportProgress::get($token);

        if ($status === null) {
            return response()->json(['status' => 'not_found'], 404);
        }

        if (($status['user_id'] ?? null) !== $request->user()?->id) {
            abort(403);
        }

        return response()->json($status);
    }

    public function downloadImportTemplate(): BinaryFileResponse
    {
        return $this->templateService->download();
    }

    public function previewImport(ImportHajjParticipantRequest $request): RedirectResponse
    {
        $payload = $request->validated();
        $path = $request->file('file')->getRealPath();

        try {
            $preview = $this->importService->preview($path, (int) $payload['tahun_haji']);
        } catch (RuntimeException $e) {
            return back()->withErrors(['file' => $e->getMessage()]);
        }

        return back()->with('import_preview', $preview);
    }
}
