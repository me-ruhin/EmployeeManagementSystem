<div>
    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-lg font-medium text-gray-900">Attendance Management</h2>
        <div>
            <a href="{{ route('attendance.check-in-out') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                <i class="fas fa-clock mr-2"></i> Check In/Out
            </a>
            @if(auth()->user()->isAdmin() || auth()->user()->isHR())
            <button wire:click="generateReport" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 ml-2">
                <i class="fas fa-file-export mr-2"></i> Generate Report
            </button>
            @endif
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
            <p>{{ session('success') }}</p>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
            <p>{{ session('error') }}</p>
        </div>
    @endif

    <!-- Filters -->
    <div class="bg-white rounded-md shadow-sm p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                <input wire:model="date" type="date" id="date" class="w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            </div>
            <div>
                <label for="department_id" class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                <select wire:model="department_id" id="department_id" class="w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <option value="">All Departments</option>
                    @foreach($viewingDepartments as $department)
                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select wire:model="status" id="status" class="w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <option value="">All Status</option>
                    <option value="Present">Present</option>
                    <option value="Absent">Absent</option>
                    <option value="Late">Late</option>
                    <option value="On Leave">On Leave</option>
                </select>
            </div>
            
            @if(auth()->user()->isAdmin() || auth()->user()->isHR())
            <div>
                <label for="report-type" class="block text-sm font-medium text-gray-700 mb-1">Report Type</label>
                <select wire:model="reportType" id="report-type" class="w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <option value="daily">Daily Report</option>
                    <option value="monthly">Monthly Report</option>
                    <option value="custom">Custom Range</option>
                </select>
            </div>
            @endif
        </div>
        
        @if($reportType === 'custom')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                <input wire:model="startDate" type="date" id="start_date" class="w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            </div>
            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                <input wire:model="endDate" type="date" id="end_date" class="w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            </div>
        </div>
        @elseif($reportType === 'monthly')
        <div class="mt-4">
            <label for="month" class="block text-sm font-medium text-gray-700 mb-1">Month</label>
            <input wire:model="month" type="month" id="month" class="w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
        </div>
        @endif
    </div>

    <!-- Attendance Table -->
    <div class="bg-white shadow-sm rounded-md overflow-hidden mb-6">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check In</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check Out</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hours</th>
                        @if(auth()->user()->isAdmin() || auth()->user()->isHR() || auth()->user()->isManager() || auth()->user()->isDepartmentHead())
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($attendances as $attendance)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    @if ($attendance->employee->user->profile_photo)
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <img class="h-10 w-10 rounded-full" src="{{ Storage::url($attendance->employee->user->profile_photo) }}" alt="{{ $attendance->employee->user->name }}">
                                        </div>
                                    @else
                                        <div class="flex-shrink-0 h-10 w-10 bg-gray-200 rounded-full flex items-center justify-center">
                                            <i class="fas fa-user text-gray-400"></i>
                                        </div>
                                    @endif
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $attendance->employee->user->name }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $attendance->employee->department->name ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $attendance->date->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $attendance->check_in_time ? $attendance->check_in_time->format('h:i A') : '--' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $attendance->check_out_time ? $attendance->check_out_time->format('h:i A') : '--' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    @if($attendance->status === 'Present') bg-green-100 text-green-800 
                                    @elseif($attendance->status === 'Absent') bg-red-100 text-red-800
                                    @elseif($attendance->status === 'Late') bg-yellow-100 text-yellow-800
                                    @else bg-blue-100 text-blue-800 @endif">
                                    {{ $attendance->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $attendance->hours_worked > 0 ? number_format($attendance->hours_worked, 2) : '--' }}
                            </td>
                            @if(auth()->user()->isAdmin() || auth()->user()->isHR() || auth()->user()->isManager() || auth()->user()->isDepartmentHead())
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    <button wire:click="markPresent('{{ $attendance->employee_id }}')" class="text-green-600 hover:text-green-900" title="Mark Present">
                                        <i class="fas fa-check-circle"></i>
                                    </button>
                                    <button wire:click="markLate('{{ $attendance->employee_id }}')" class="text-yellow-600 hover:text-yellow-900" title="Mark Late">
                                        <i class="fas fa-clock"></i>
                                    </button>
                                    <button wire:click="markAbsent('{{ $attendance->employee_id }}')" class="text-red-600 hover:text-red-900" title="Mark Absent">
                                        <i class="fas fa-times-circle"></i>
                                    </button>
                                    <button wire:click="markOnLeave('{{ $attendance->employee_id }}')" class="text-blue-600 hover:text-blue-900" title="Mark On Leave">
                                        <i class="fas fa-calendar-minus"></i>
                                    </button>
                                </div>
                            </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->isAdmin() || auth()->user()->isHR() || auth()->user()->isManager() || auth()->user()->isDepartmentHead() ? '8' : '7' }}" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                                No attendance records found for the selected criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="px-6 py-3 bg-gray-50">
            {{ $attendances->links() }}
        </div>
    </div>

    <!-- Employees Without Attendance -->
    @if(auth()->user()->isAdmin() || auth()->user()->isHR() || auth()->user()->isManager() || auth()->user()->isDepartmentHead())
    <div class="bg-white shadow-sm rounded-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Employees Without Attendance</h3>
            <p class="mt-1 text-sm text-gray-600">
                These employees don't have attendance records for the selected date.
            </p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($employeesWithoutAttendance as $employee)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    @if ($employee->user->profile_photo)
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <img class="h-10 w-10 rounded-full" src="{{ Storage::url($employee->user->profile_photo) }}" alt="{{ $employee->user->name }}">
                                        </div>
                                    @else
                                        <div class="flex-shrink-0 h-10 w-10 bg-gray-200 rounded-full flex items-center justify-center">
                                            <i class="fas fa-user text-gray-400"></i>
                                        </div>
                                    @endif
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $employee->user->name }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $employee->user->email }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $employee->department->name ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    <button wire:click="markPresent('{{ $employee->id }}')" class="text-green-600 hover:text-green-900" title="Mark Present">
                                        <i class="fas fa-check-circle"></i>
                                    </button>
                                    <button wire:click="markLate('{{ $employee->id }}')" class="text-yellow-600 hover:text-yellow-900" title="Mark Late">
                                        <i class="fas fa-clock"></i>
                                    </button>
                                    <button wire:click="markAbsent('{{ $employee->id }}')" class="text-red-600 hover:text-red-900" title="Mark Absent">
                                        <i class="fas fa-times-circle"></i>
                                    </button>
                                    <button wire:click="markOnLeave('{{ $employee->id }}')" class="text-blue-600 hover:text-blue-900" title="Mark On Leave">
                                        <i class="fas fa-calendar-minus"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                                All employees have attendance records for the selected date.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
