<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * تحديد التقارير التي يحق للمستخدم رؤيتها
     */
    private function visibleReportsQuery($user)
    {
        // المدير العام يرى جميع التقارير
        if ($user->role === 'admin') {
            return Report::query();
        }

        // مدير القسم يرى تقارير قسمه فقط
        if ($user->role === 'department_manager') {
            return Report::where(
                'department_id',
                $user->department_id
            );
        }

        // المستخدم العادي يرى تقاريره الشخصية فقط
        return Report::where('user_id', $user->id);
    }

    /**
     * التحقق من صلاحية إدارة التقرير
     */
    private function canManageReport($user, Report $report)
    {
        // المدير العام يستطيع إدارة جميع التقارير
        if ($user->role === 'admin') {
            return true;
        }

        // مدير القسم يستطيع إدارة تقارير قسمه فقط
        if ($user->role === 'department_manager') {
            return $user->department_id !== null
                && (int) $report->department_id
                    === (int) $user->department_id;
        }

        return false;
    }

   
    public function index(Request $request)
    {
        $user = $request->user();

        $reports = $this->visibleReportsQuery($user)
            ->latest()
            ->paginate(15);

        return response()->json([
            'message' => 'تم جلب التقارير بنجاح',
            'reports' => $reports,
        ], 200);
    }

   
public function store(Request $request)
{
    $user = $request->user();

    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'required|string|max:10000',
        'department_id' => 'required|exists:departments,id',
    ]);

    $report = $user->reports()->create([
        'title' => $validated['title'],
        'description' => $validated['description'],
        'department_id' => $validated['department_id'],
        'status' => 'pending',
    ]);

    return response()->json([
        'message' => 'تم إنشاء التقرير بنجاح',
        'report' => $report,
    ], 201);
}

 
    public function show(Request $request, $id)
    {
        $user = $request->user();

        $report = $this->visibleReportsQuery($user)
            ->find($id);

        if (!$report) {
            return response()->json([
                'message' => 'التقرير غير موجود أو ليس لديك صلاحية لعرضه',
            ], 404);
        }

        return response()->json([
            'message' => 'تم جلب التقرير بنجاح',
            'report' => $report,
        ], 200);
    }

   
    public function update(Request $request, $id)
    {
        $user = $request->user();

        $report = Report::find($id);

        if (!$report) {
            return response()->json([
                'message' => 'التقرير غير موجود',
            ], 404);
        }

        if (!$this->canManageReport($user, $report)) {
            return response()->json([
                'message' => 'ليس لديك صلاحية لتعديل هذا التقرير',
            ], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string|max:10000',
        ]);

        $report->update($validated);

        return response()->json([
            'message' => 'تم تعديل التقرير بنجاح',
            'report' => $report->fresh(),
        ], 200);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        $report = Report::find($id);

        if (!$report) {
            return response()->json([
                'message' => 'التقرير غير موجود',
            ], 404);
        }

        if (!$this->canManageReport($user, $report)) {
            return response()->json([
                'message' => 'ليس لديك صلاحية لحذف هذا التقرير',
            ], 403);
        }

        $report->delete();

        return response()->json([
            'message' => 'تم حذف التقرير بنجاح',
        ], 200);
    }
}