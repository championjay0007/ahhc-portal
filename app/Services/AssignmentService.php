<?php

namespace App\Services;

use App\Models\ParticipantAssignment;
use App\Models\Worker;

class AssignmentService
{
    public function __construct(private ComplianceService $complianceService) {}

    /**
     * Create a participant assignment with compliance validation
     */
    public function createAssignment(
        int $participantId,
        int $workerId,
        ?int $supportPersonId,
        string $startDate,
        ?string $endDate,
        string $assignmentType,
        string $status = 'active',
        bool $isPrimary = false
    ): ParticipantAssignment|array {
        $worker = Worker::findOrFail($workerId);

        // Check if worker can be assigned based on compliance
        if (! $this->complianceService->canWorkerBeAssigned($worker)) {
            return [
                'success' => false,
                'message' => $this->complianceService->getAssignmentBlockingReason($worker),
                'issues' => $this->complianceService->getWorkerCriticalIssues($worker)->toArray(),
            ];
        }

        return ParticipantAssignment::create([
            'participant_id' => $participantId,
            'worker_id' => $workerId,
            'support_person_id' => $supportPersonId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'assignment_type' => $assignmentType,
            'status' => $status,
            'is_primary' => $isPrimary,
        ]);
    }

    /**
     * Update an assignment
     */
    public function updateAssignment(
        ParticipantAssignment $assignment,
        array $data
    ): ParticipantAssignment|array {
        // If changing worker, check compliance
        if (isset($data['worker_id']) && $data['worker_id'] !== $assignment->worker_id) {
            $newWorker = Worker::findOrFail($data['worker_id']);

            if (! $this->complianceService->canWorkerBeAssigned($newWorker)) {
                return [
                    'success' => false,
                    'message' => $this->complianceService->getAssignmentBlockingReason($newWorker),
                    'issues' => $this->complianceService->getWorkerCriticalIssues($newWorker)->toArray(),
                ];
            }
        }

        $assignment->update($data);

        return $assignment->fresh();
    }

    /**
     * Get all active assignments for a worker
     */
    public function getWorkerActiveAssignments(Worker $worker)
    {
        return $worker->assignments()
            ->where('status', 'active')
            ->where('end_date', '>=', now()->toDateString())
            ->get();
    }

    /**
     * Check if worker can be reassigned (compliance-wise)
     */
    public function canWorkerBeReassigned(Worker $worker): bool
    {
        return $this->complianceService->canWorkerBeAssigned($worker);
    }

    /**
     * Get blocking reason for worker
     */
    public function getBlockingReason(Worker $worker): ?string
    {
        return $this->complianceService->getAssignmentBlockingReason($worker);
    }
}
