@extends('layouts.app')

@section('title', @i18('customers') . ' - Arcadia CRM')

@section('content')
<div class="bg-white shadow rounded-lg">
    <div class="px-4 py-5 sm:p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900">@i18('customers')</h2>
            <a href="/customers/create" class="btn-primary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                @i18('create') @i18('customer')
            </a>
        </div>

        <!-- Filtry -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                                         <input type="text" placeholder="@i18('search') @i18('customers')..." class="form-input">
                </div>
                <select class="form-input w-full sm:w-48 dropdown-up">
                                         <option>@i18('all_categories')</option>
                     <option>@i18('category_company')</option>
                     <option>@i18('category_person')</option>
                </select>
            </div>
        </div>

        <!-- Tabulka zákazníků -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                             @i18('customer_name')
                         </th>
                         <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                             @i18('customer_email')
                         </th>
                         <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                             @i18('customer_phone')
                         </th>
                         <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                             @i18('customer_category')
                         </th>
                         <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                             @i18('actions')
                         </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @if(isset($customers) && count($customers) > 0)
                        @foreach($customers as $customer)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-primary-100 flex items-center justify-center">
                                            <span class="text-primary-600 font-medium">{{ substr($customer->getName(), 0, 1) }}</span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $customer->getName() }}</div>
                                         <div class="text-sm text-gray-500">{{ $customer->getCompany() ?? i18('category_person') }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $customer->getEmail() }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $customer->getPhone() }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $customer->getCategory() === 'company' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                {{ $customer->getCategory() === 'company' ? i18('category_company') : i18('category_person') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                     <a href="/customers/{{ $customer->getId() }}" class="text-primary-600 hover:text-primary-900">@i18('view')</a>
                                     <a href="/customers/{{ $customer->getId() }}/edit" class="text-gray-600 hover:text-gray-900">@i18('edit')</a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    @else
                        <tr>
                             <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                 @i18('no_customers_found')
                             </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <!-- Paginace -->
        @if(isset($pagination))
        <div class="mt-6 flex items-center justify-between 2">
            <div class="text-sm text-gray-700">
                                 @i18('showing') {{ $pagination->from }} - {{ $pagination->to }} @i18('of') {{ $pagination->total }} @i18('customers')
            </div>
            <div class="flex space-x-2">
                @if($pagination->currentPage > 1)
                                         <a href="?page={{ $pagination->currentPage - 1 }}" class="btn-secondary">@i18('previous')</a>
                @endif
                @if($pagination->currentPage < $pagination->lastPage)
                                         <a href="?page={{ $pagination->currentPage + 1 }}" class="btn-secondary">@i18('next')</a>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
