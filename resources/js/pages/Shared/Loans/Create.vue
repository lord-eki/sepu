<template>
    <AppLayout :breadcrumbs="[{ title: 'Loans', href: isMemberRole ? route('my-loans') : route('loans.index') }, { title: 'Apply' }]">
        <Head title="New Loan Application" />
        <!-- Flash messages -->
        <div class="">
            <transition
                enter-active-class="transition ease-out duration-300"
                enter-from-class="opacity-0 -translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition ease-in duration-200"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-2"
            >
                <div
                    v-if="successMessage || errorMessages"
                    :class="[
                        successMessage ? 'border-emerald-300 bg-emerald-100 text-emerald-900' : 'border-rose-300 bg-rose-100 text-rose-900',
                        'relative mx-auto mb-4 flex w-fit items-start gap-4 rounded-xl px-6 py-3 shadow-md',
                    ]"
                >
                    <div class="flex-1">
                        <p v-if="successMessage">{{ successMessage }}</p>
                        <ul v-else class="list-disc space-y-1 pl-5">
                            <li v-for="(errs, field) in errorMessages" :key="field">
                                <span v-if="field !== 'general'"
                                    ><strong class="capitalize">{{ field }}:</strong>
                                </span>
                                <span v-if="Array.isArray(errs)">
                                    <!-- <span v-for="(err, i) in errs" :key="i" class="block">{{ err }}</span> -->
                                    <span v-for="(err, i) in errs" :key="i" class="block">
                                        <span class="whitespace-pre-wrap"> {{ err }}</span>
                                    </span>
                                </span>
                                <span v-else>{{ errs }}</span>
                            </li>
                        </ul>
                    </div>
                    <button
                        type="button"
                        class="ml-2 text-gray-400 hover:text-gray-200"
                        @click="
                            () => {
                                successMessage = null;
                                errorMessages = null;
                            }
                        "
                    >
                        X
                    </button>
                </div>
            </transition>
        </div>

        <!-- Dark modern background -->
        <div class="min-h-screen bg-gradient-to-b from-blue-50 via-white to-orange-50 px-4 py-8 text-gray-800 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-4xl">
                <!-- Header -->
                <div class="mb-8 flex items-center justify-between">
                    <div>
                        <h1 class="text-xl font-extrabold tracking-tight text-[rgb(7,40,75)] sm:text-2xl">New Loan Application</h1>
                        <p v-if="isEligible" class="mt-1 text-sm text-blue-900">Complete this form to apply for a loan.</p>
                        <p v-else class="mt-1 text-sm text-blue-900">To apply loan you must meet all the requirements.</p>
                    </div>

                    <Link
                        :href="isMemberRole ? route('my-loans') : route('loans.index')"
                        class="inline-flex items-center gap-2 rounded-xl bg-orange-500 px-4 py-2 text-sm font-medium text-white shadow-lg transition hover:bg-orange-600"
                    >
                        <ArrowLeft class="h-4 w-4" />
                        <p>Back&nbsp;<span class="max-sm:hidden">to Loans</span></p>
                    </Link>
                </div>

                <!-- Eligibility Check Message -->
                <div v-if="!isEligible" class="mb-8 rounded-xl border border-rose-200 bg-rose-50 p-6 text-rose-800 shadow">
                    <h3 class="mb-2 text-lg font-semibold">
                        <template v-if="isMemberRole"> You are currently not eligible for a loan. </template>
                        <template v-else> Loan Application Not Allowed </template>
                    </h3>

                    <p class="mb-3 text-sm">
                        <template v-if="isMemberRole"> Reason(s): </template>
                        <template v-else>
                            The selected member with membership ID
                            <strong>{{ memberInfo.membership_id }}</strong>
                            is currently <strong>not eligible</strong> for a loan. Reason(s):
                        </template>
                    </p>

                    <ul class="mb-4 list-disc space-y-1 pl-6 text-sm">
                        <li v-for="(reason, i) in ineligibleReasons" :key="i">{{ reason }}</li>
                    </ul>

                    <!-- Only show back button for non-member users -->
                    <div v-if="!isMemberRole" class="flex justify-end">
                        <button
                            type="button"
                            @click="resetMemberSelection"
                            class="inline-flex items-center gap-2 rounded-lg bg-blue-900/90 px-4 py-2 text-sm text-white shadow transition hover:bg-[rgba(7,40,75,0.85)]"
                        >
                            <ArrowLeft class="h-4 w-4" />
                            <span>Go Back to Member Selection</span>
                        </button>
                    </div>
                </div>

                <!-- Loan Application Form -->
                <form v-else @submit.prevent="submitApplication" class="space-y-8">
                    <!-- Member Information (card) -->
                    <section class="overflow-hidden rounded-2xl border border-gray-100 bg-white text-gray-900 shadow-xl">
                        <div class="bg-blue-900/90 px-6 py-3">
                            <h3 class="text-base font-semibold text-white sm:text-lg">Member Information</h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div v-if="!isMemberRole">
                                    <label for="member_id" class="block text-sm font-medium text-gray-700">Select Member *</label>
                                    <select
                                        v-model="form.member_id"
                                        @change="onMemberChange"
                                        id="member_id"
                                        required
                                        class="mt-2 block w-full rounded-lg border border-gray-300 p-3 focus:ring-2 focus:ring-orange-400 focus:outline-none"
                                    >
                                        <option value="">Choose a member...</option>
                                        <option v-for="member in members" :key="member.id" :value="member.id">
                                            {{ member.first_name }} {{ member.last_name }} - {{ member.membership_id }}
                                        </option>
                                    </select>
                                </div>

                                <div v-if="selectedMember || isMemberRole">
                                    <label class="block text-sm font-medium text-gray-700">Member Details</label>
                                    <div class="mt-2 rounded-lg border border-gray-200 bg-gray-50 p-4">
                                        <p class="text-sm font-semibold text-gray-900">{{ memberInfo.first_name }} {{ memberInfo.last_name }}</p>
                                        <p class="text-sm text-gray-500">{{ memberInfo.membership_id }}</p>
                                        <p class="text-sm text-gray-500">Phone: {{ auth.user.phone }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Loan Product -->
                    <section class="overflow-hidden rounded-2xl border border-gray-100 bg-white text-gray-900 shadow-xl">
                        <div class="bg-blue-900/90 px-6 py-3">
                            <h3 class="text-base font-semibold text-white sm:text-lg">Loan Product</h3>
                        </div>
                        <div class="p-6">
                            <label for="loan_product_id" class="block text-sm font-medium text-gray-700">Select Loan Product *</label>
                            <select
                                v-model="form.loan_product_id"
                                @change="onProductChange"
                                id="loan_product_id"
                                required
                                class="mt-2 block w-full rounded-lg border border-gray-300 p-3 focus:ring-2 focus:ring-orange-400 focus:outline-none"
                            >
                                <option value="">Choose a loan product...</option>
                                <option v-for="product in loanProducts" :key="product.id" :value="product.id">
                                    {{ product.name }} - {{ product.interest_rate }}% per month
                                </option>
                            </select>

                            <div v-if="selectedProduct" class="mt-4 rounded-lg border border-blue-100 bg-[rgba(232,240,255,0.95)] p-4">
                                <h4 class="mb-2 font-semibold text-blue-900">Product Details</h4>
                                <div class="grid grid-cols-2 gap-4 text-sm md:grid-cols-4">
                                    <div>
                                        <span class="text-blue-700">Interest Rate:</span>
                                        <p class="font-medium">{{ selectedProduct.interest_rate }}% p.m</p>
                                    </div>
                                    <div>
                                        <span class="text-blue-700">Amount Range:</span>
                                        <p class="font-medium">
                                            KES {{ formatCurrency(selectedProduct.min_amount) }} - {{ formatCurrency(selectedProduct.max_amount) }}
                                        </p>
                                    </div>
                                    <div>
                                        <span class="text-blue-700">Term Range:</span>
                                        <p class="font-medium">
                                            {{ selectedProduct.min_term_months }} - {{ selectedProduct.max_term_months }}
                                            months
                                        </p>
                                    </div>
                                    <div>
                                        <span class="text-blue-700">Processing Fee:</span>
                                        <p class="font-medium">{{ selectedProduct.processing_fee_rate }}%</p>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <span class="text-blue-700">Description:</span>
                                    <p class="text-sm text-gray-700">{{ selectedProduct.description }}</p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Loan Details -->
                    <section class="overflow-hidden rounded-2xl border border-gray-100 bg-white text-gray-900 shadow-xl">
                        <div class="bg-blue-900/90 px-6 py-3">
                            <h3 class="text-base font-semibold text-white sm:text-lg">Loan Details</h3>
                        </div>
                        <div class="grid grid-cols-1 gap-6 p-6 md:grid-cols-2">
                            <div>
                                <label for="amount" class="block text-sm font-medium text-gray-700">Loan Amount *</label>
                                <input
                                    v-model="form.applied_amount"
                                    type="number"
                                    id="amount"
                                    required
                                    class="mt-2 block w-full rounded-lg border border-gray-300 p-3 focus:ring-2 focus:ring-orange-400 focus:outline-none"
                                />
                            </div>
                            <div>
                                <label for="term" class="block text-sm font-medium text-gray-700">Repayment Term (months) *</label>
                                <input
                                    v-model="form.term_months"
                                    type="number"
                                    id="term"
                                    required
                                    class="mt-2 block w-full rounded-lg border border-gray-300 p-3 focus:ring-2 focus:ring-orange-400 focus:outline-none"
                                />
                            </div>

                            <div class="md:col-span-2">
                                <label for="purpose" class="block text-sm font-medium text-gray-700">Purpose *</label>
                                <textarea
                                    v-model="form.purpose"
                                    id="purpose"
                                    rows="3"
                                    required
                                    class="mt-2 block w-full rounded-lg border border-gray-300 p-3 focus:ring-2 focus:ring-orange-400 focus:outline-none"
                                ></textarea>
                            </div>

                            <!-- Repayment preview (if available) -->
                            <div v-if="loanCalculation" class="mt-2 rounded-lg border border-gray-200 bg-gray-50 p-4 md:col-span-2">
                                <h4 class="text-sm font-medium text-gray-800">Repayment estimate</h4>
                                <div class="mt-2 text-sm text-gray-700">
                                    <div>Monthly Repayment: KES {{ formatCurrency(loanCalculation.monthlyRepayment.toFixed(2)) }}</div>
                                    <div>Total Repayable: KES {{ formatCurrency(loanCalculation.totalRepayable.toFixed(2)) }}</div>
                                    <div>Interest: KES {{ formatCurrency(loanCalculation.interestAmount.toFixed(2)) }}</div>
                                    <div>Processing Fee: KES {{ formatCurrency(loanCalculation.processingFee.toFixed(2)) }}</div>
                                    <div>Insurance Fee: KES {{ formatCurrency(loanCalculation.insuranceFee.toFixed(2)) }}</div>
                                    <div>Net Amount (after fees): KES {{ formatCurrency(loanCalculation.netAmount.toFixed(2)) }}</div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Disbursement -->
                    <section class="overflow-hidden rounded-2xl border border-gray-100 bg-white text-gray-900 shadow-xl">
                        <div class="bg-blue-900/90 px-6 py-3">
                            <h3 class="text-base font-semibold text-white sm:text-lg">Disbursement Method</h3>
                        </div>
                        <div class="space-y-4 p-6">
                            <div>
                                <label for="disbursement" class="block text-sm font-medium text-gray-700">Choose Method *</label>
                                <select
                                    id="disbursement"
                                    v-model="disbursementMethod"
                                    required
                                    class="mt-2 block w-full rounded-lg border border-gray-300 p-3 focus:ring-2 focus:ring-orange-400 focus:outline-none"
                                >
                                    <option value="">-- Select Method --</option>
                                    <option value="mpesa">M-Pesa</option>
                                    <option value="bank">Bank</option>
                                    <option value="cheque">Cheque</option>
                                </select>
                            </div>

                            <div v-if="disbursementMethod === 'mpesa'">
                                <label for="mpesaNumber" class="block text-sm font-medium text-gray-700">M-Pesa Number *</label>
                                <input
                                    id="mpesaNumber"
                                    type="text"
                                    v-model="disbursementDetails.mpesaNumber"
                                    placeholder="e.g. 0712345678"
                                    required
                                    class="mt-2 block w-full rounded-lg border border-gray-300 p-3 focus:ring-2 focus:ring-orange-400 focus:outline-none"
                                />
                            </div>

                            <div v-if="disbursementMethod === 'bank'">
                                <label for="bankName" class="block text-sm font-medium text-gray-700">Bank Name *</label>
                                <input
                                    id="bankName"
                                    type="text"
                                    v-model="disbursementDetails.bankName"
                                    placeholder="e.g. Equity Bank"
                                    required
                                    class="mt-2 block w-full rounded-lg border border-gray-300 p-3 focus:ring-2 focus:ring-orange-400 focus:outline-none"
                                />

                                <div class="mt-3 grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div>
                                        <label for="branch" class="block text-sm font-medium text-gray-700">Branch *</label>
                                        <input
                                            id="branch"
                                            type="text"
                                            v-model="disbursementDetails.branch"
                                            placeholder="e.g. Nairobi CBD"
                                            required
                                            class="mt-2 block w-full rounded-lg border border-gray-300 p-3 focus:ring-2 focus:ring-orange-400 focus:outline-none"
                                        />
                                    </div>
                                    <div>
                                        <label for="accountNumber" class="block text-sm font-medium text-gray-700">Account Number *</label>
                                        <input
                                            id="accountNumber"
                                            type="text"
                                            v-model="disbursementDetails.accountNumber"
                                            placeholder="e.g. 123456789"
                                            required
                                            class="mt-2 block w-full rounded-lg border border-gray-300 p-3 focus:ring-2 focus:ring-orange-400 focus:outline-none"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div v-if="disbursementMethod === 'cheque'">
                                <label for="payee" class="block text-sm font-medium text-gray-700">Payee Name *</label>
                                <input
                                    id="payee"
                                    type="text"
                                    v-model="disbursementDetails.payee"
                                    placeholder="Enter Payee Name"
                                    required
                                    class="mt-2 block w-full rounded-lg border border-gray-300 p-3 focus:ring-2 focus:ring-orange-400 focus:outline-none"
                                />
                            </div>
                        </div>
                    </section>

                    <!-- Guarantors -->
                    <section class="relative overflow-hidden overflow-visible rounded-2xl border border-gray-100 bg-white text-gray-900 shadow-xl">
                        <div class="bg-blue-900/90 px-6 py-3">
                            <h3 class="text-base font-semibold text-white sm:text-lg">Guarantors</h3>
                        </div>
                        <div class="space-y-4 p-6">
                            <p class="mb-2 text-sm text-gray-600">
                                Select guarantors from members. <span class="font-medium text-blue-900">Selected guarantors will be notified.</span>
                            </p>

                            <div v-for="(guarantor, index) in form.guarantors" :key="index" class="space-y-4 rounded-lg border p-4">
                                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                    <div>
                                        <!-- Select -->
                                        <!-- SEARCH INPUT -->
                                        <div class="relative">
                                            <label class="block text-sm font-medium text-gray-700"> Select Member * </label>

                                            <!-- Input (styled like your other fields) -->
                                            <input
                                                v-model="guarantorSearch[index]"
                                                @input="handleSearchInput(index)"
                                                @focus="openSearchIndex = index"
                                                @blur="setTimeout(() => (openSearchIndex = null), 200)"
                                                placeholder="Search member by name or ID..."
                                                class="mt-2 block w-full rounded-lg border border-gray-300 bg-white p-3 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none"
                                            />

                                            <!-- DROPDOWN LIST -->
                                            <div
                                                v-if="openSearchIndex === index && filteredMembers(index).length"
                                                class="absolute z-50 mt-1 max-h-60 w-full overflow-y-auto rounded-lg border bg-white shadow-lg"
                                            >
                                                <div
                                                    v-for="member in filteredMembers(index)"
                                                    :key="member.id"
                                                    @mousedown.prevent="
                                                        guarantor.member_id = member.id;
                                                        guarantorSearch[index] = member.first_name + ' ' + member.last_name;
                                                        openSearchIndex = null;
                                                    "
                                                    class="cursor-pointer px-4 py-2 text-sm transition hover:bg-blue-50"
                                                >
                                                    <div class="font-medium text-gray-900">{{ member.first_name }} {{ member.last_name }}</div>
                                                    <div class="text-xs text-gray-500">
                                                        {{ member.membership_id }}
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- SELECTED DISPLAY -->
                                            <div v-if="guarantor.member_id" class="mt-2 text-sm text-emerald-700">
                                                Selected:
                                                {{ props.members.find((m) => m.id === guarantor.member_id)?.first_name }}
                                                {{ props.members.find((m) => m.id === guarantor.member_id)?.last_name }}
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Guaranteed Amount (KES) *</label>
                                        <input
                                            v-model="guarantor.guaranteed_amount"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            :max="form.applied_amount"
                                            required
                                            class="mt-2 block w-full rounded-lg border border-gray-300 p-3"
                                        />
                                    </div>
                                </div>

                                <div class="flex justify-end">
                                    <button
                                        type="button"
                                        @click="removeGuarantor(index)"
                                        class="text-sm font-medium text-rose-600 hover:text-rose-800"
                                    >
                                        ✕ Remove Guarantor
                                    </button>
                                </div>
                            </div>

                            <div>
                                <button
                                    type="button"
                                    @click="addGuarantor"
                                    class="inline-flex items-center gap-2 rounded-lg bg-orange-500 px-3 py-2 text-white shadow hover:bg-orange-600"
                                >
                                    + Add Guarantor
                                </button>
                            </div>
                        </div>
                    </section>

                    <!-- Support Documents -->
                    <section class="overflow-hidden rounded-2xl border border-gray-100 bg-white text-gray-900 shadow-xl">
                        <div class="bg-blue-900/90 px-6 py-3">
                            <h3 class="text-base font-semibold text-white sm:text-lg">Support Documents</h3>
                        </div>
                        <div class="space-y-4 p-6">
                            <!-- Instructions -->
                            <div class="rounded-lg border border-blue-100 bg-blue-50 p-4 text-sm text-blue-900">
                                <strong>Important: document(s) with asterisk(*) are mandatory</strong>
                                <ul class="mt-1 list-disc space-y-1 pl-6">
                                    <li>Current Payslip&nbsp;<span class="text-lg font-semibold text-red-600">*</span></li>
                                    <li>ID Copy</li>
                                    <li>Bank Statement</li>
                                    <li>Evidence of Business</li>
                                </ul>
                            </div>

                            <!-- File Upload Control -->
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                                <button
                                    type="button"
                                    @click="showDocTypeSelector = true"
                                    class="inline-flex w-fit items-center gap-2 rounded-lg bg-blue-900/90 px-4 py-2 text-white shadow hover:bg-[rgba(7,40,75,0.85)]"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Choose Document
                                </button>

                                <!-- Selected Files Display -->
                                <div class="min-w-0 flex-1">
                                    <template v-if="uploadedFiles.multiple.length">
                                        <strong class="mb-1 block text-sm">Uploaded Documents:</strong>
                                        <ul class="list-disc space-y-1 pl-5 text-sm">
                                            <li
                                                v-for="(file, index) in uploadedFiles.multiple"
                                                :key="file.type"
                                                class="flex items-center justify-between"
                                            >
                                                <div>
                                                    <span class="font-medium">{{ file.type }}</span> —
                                                    {{ file.file.name }}
                                                    <span class="text-xs text-gray-500">({{ humanFileSize(file.file.size) }})</span>
                                                </div>
                                                <button
                                                    type="button"
                                                    @click="removeSingleFile(file.type)"
                                                    class="text-xs text-rose-600 hover:text-rose-800"
                                                >
                                                    ✕ Remove
                                                </button>
                                            </li>
                                        </ul>
                                    </template>
                                    <div v-else class="text-sm text-gray-400 italic">
                                        No files uploaded yet — upload one or more required documents (PDF only, max 10MB each).
                                    </div>
                                </div>

                                <!-- Remove All -->
                                <div v-if="uploadedFiles.multiple.length" class="flex flex-col gap-2">
                                    <p v-if="!hasMandatoryPayslip" class="text-sm text-red-600">Current Payslip is required.</p>

                                    <p v-else class="text-sm text-green-600">✅ Mandatory document uploaded.</p>
                                    <button type="button" @click="removeSupportFiles" class="text-sm text-rose-600 hover:text-rose-800">
                                        Remove All
                                    </button>
                                </div>
                            </div>

                            <!-- Modal: Choose Document Type -->
                            <div v-if="showDocTypeSelector" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4">
                                <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                                    <h4 class="mb-4 text-lg font-semibold">Select Document Type</h4>
                                    <div class="space-y-3">
                                        <button
                                            v-for="doc in remainingDocTypes"
                                            :key="doc.name"
                                            @click="chooseDocType(doc.name)"
                                            class="w-full rounded-lg border px-4 py-2 text-left hover:bg-blue-50"
                                        >
                                            {{ doc.name }}
                                            <span v-if="doc.required" class="font-bold text-red-600"> * </span>
                                        </button>
                                    </div>
                                    <div class="mt-4 flex justify-end">
                                        <button
                                            @click="showDocTypeSelector = false"
                                            class="rounded-md bg-slate-300 px-3 py-2 text-sm text-gray-600 hover:cursor-pointer hover:bg-slate-200 hover:text-gray-800"
                                        >
                                            Cancel
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Hidden File Input -->
                            <input ref="supportFileInput" type="file" accept="application/pdf" class="hidden" @change="handleSupportFiles" />

                            <p class="mt-2 text-xs text-gray-400">Only PDF files allowed. Maximum 10MB each.</p>
                        </div>
                    </section>

                    <!-- Submit row -->
                    <div class="flex items-center justify-end gap-4">
                        <Link
                            :href="isMemberRole ? route('my-loans') : route('loans.index')"
                            class="rounded-lg bg-gray-100 px-5 py-3 text-gray-800 shadow hover:bg-gray-200"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            :disabled="!canSubmit || processing"
                            class="inline-flex items-center gap-3 rounded-xl bg-blue-900/90 px-6 py-3 font-medium text-white shadow hover:cursor-pointer hover:bg-orange-600 disabled:bg-gray-400"
                        >
                            <svg
                                v-if="processing"
                                class="h-5 w-5 animate-spin text-white"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                            </svg>
                            <span v-if="processing">Loading...</span>
                            <span v-else>Preview Application</span>
                        </button>
                    </div>
                </form>

                <!-- Confirmation modal -->
                <div v-if="showConfirm" class="fixed inset-0 z-50 flex items-center justify-center bg-[rgba(0,0,0,0.7)] p-4">
                    <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl bg-white p-6 shadow-2xl">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900">Review Your Loan Application</h3>

                        <div class="space-y-3 text-sm text-gray-700">
                            <div>
                                <span class="font-medium text-gray-900">Member:</span> {{ memberInfo.first_name }} {{ memberInfo.last_name }}
                                <span class="text-xs text-gray-500">({{ memberInfo.membership_id }})</span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-900">Loan Product:</span>
                                {{ loanProducts.find((p) => p.id === form.loan_product_id)?.name }}
                            </div>
                            <div><span class="font-medium text-gray-900">Applied Amount:</span> KES {{ form.applied_amount }}</div>
                            <div><span class="font-medium text-gray-900">Term:</span> {{ form.term_months }} months</div>
                            <div><span class="font-medium text-gray-900">Purpose:</span> {{ form.purpose }}</div>

                            <div v-if="form.guarantors.length">
                                <span class="font-medium text-gray-900">Guarantors:</span>
                                <ul class="mt-1 ml-6 list-disc">
                                    <li v-for="(g, index) in form.guarantors" :key="index">
                                        {{ members.find((m) => m.id === g.member_id)?.first_name }}
                                        {{ members.find((m) => m.id === g.member_id)?.last_name }} — Guaranteed: KES {{ g.guaranteed_amount }}
                                    </li>
                                </ul>
                            </div>

                            <div>
                                <span class="font-medium text-gray-900">Disbursement Method:</span> {{ disbursementMethod }}
                                <div class="mt-1 ml-4 text-sm">
                                    <div v-if="disbursementMethod === 'mpesa'">M-Pesa Number: {{ disbursementDetails.mpesaNumber }}</div>
                                    <div v-if="disbursementMethod === 'bank'">
                                        Bank: {{ disbursementDetails.bankName }}, Branch: {{ disbursementDetails.branch }}, Account:
                                        {{ disbursementDetails.accountNumber }}
                                    </div>
                                    <div v-if="disbursementMethod === 'cheque'">Payee: {{ disbursementDetails.payee }}</div>
                                </div>
                            </div>

                            <div>
                                <span class="font-medium text-gray-900">Attached Documents:</span>

                                <div class="mt-2 ml-4 space-y-1 text-sm">
                                    <!-- Combined PDF (if uploaded) -->
                                    <div v-if="uploadedFiles.combined">
                                        <svg
                                            class="mr-1 inline-block h-4 w-4 text-blue-600"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            viewBox="0 0 24 24"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                        </svg>
                                        <span class="text-gray-800">
                                            {{ uploadedFiles.combined.name || 'Combined Documents.pdf' }}
                                            <span class="text-xs text-gray-500"> ({{ humanFileSize(uploadedFiles.combined.size || 0) }}) </span>
                                        </span>
                                    </div>

                                    <!-- Individual Files -->
                                    <div v-else-if="uploadedFiles.multiple?.length">
                                        <ul class="list-disc space-y-1 pl-5 text-gray-800">
                                            <li
                                                v-for="(fileObj, index) in uploadedFiles.multiple"
                                                :key="index"
                                                class="flex items-center justify-between"
                                            >
                                                <span>
                                                    {{ fileObj.type || fileObj.file?.name }}
                                                    <span class="text-xs text-gray-500"> ({{ humanFileSize(fileObj.file?.size || 0) }}) </span>
                                                </span>
                                            </li>
                                        </ul>
                                    </div>

                                    <!-- None -->
                                    <div v-else class="text-gray-400 italic">No documents uploaded</div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end gap-3">
                            <button
                                type="button"
                                @click="showConfirm = false"
                                class="rounded-lg border border-gray-300 px-4 py-2 text-gray-700 hover:cursor-pointer hover:bg-gray-100"
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                @click="confirmSubmit"
                                class="rounded-lg bg-orange-500 px-4 py-2 text-white shadow hover:cursor-pointer hover:bg-blue-800"
                            >
                                Yes, Submit
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Fullscreen loader (subtle) -->
                <div v-if="processing" class="fixed inset-0 z-50 flex items-center justify-center bg-[rgba(0,0,0,0.6)]">
                    <div class="flex items-center gap-3 rounded-lg bg-white px-6 py-4 shadow-lg">
                        <svg class="h-6 w-6 animate-spin text-blue-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                        </svg>
                        <span class="font-medium text-gray-900">Processing your loan application...</span>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import { ArrowLeft } from 'lucide-vue-next';
import { computed, onMounted, reactive, ref, watch, watchEffect } from 'vue';

/* props from server */
const props = defineProps({
    loanProducts: Array,
    members: Array,
    auth: Object,
});

/* state */
const successMessage = ref(null);
const errorMessages = ref(null);
const processing = ref(false);
const showConfirm = ref(false);
const guarantorSearch = ref([]);
const openSearchIndex = ref(null);

const form = reactive({
    member_id: '',
    loan_product_id: '',
    applied_amount: '',
    term_months: '',
    purpose: '',
    guarantors: [],

    disbursement_method: '',
    bank_account: '',
    bank_name: '',
    mpesa_number: '',
});

function handleSearchInput(index) {
    if (!guarantorSearch.value[index]) {
        openSearchIndex.value = null;
    } else {
        openSearchIndex.value = index;
    }
}

function formatCurrency(value) {
    if (value == null || value === '' || isNaN(value)) return '0.00';
    return new Intl.NumberFormat('en-KE', {
        style: 'decimal',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(value);
}

const disbursementMethod = ref('');
const disbursementDetails = reactive({
    mpesaNumber: '',
    bankName: '',
    branch: '',
    accountNumber: '',
    payee: '',
});

const filteredMembers = (index) => {
    const search = (guarantorSearch.value[index] || '').trim().toLowerCase();

    if (!search) return [];

    return props.members.filter((member) => {
        const fullName = `${member.first_name} ${member.last_name}`.toLowerCase();
        const id = (member.membership_id || '').toLowerCase();

        const matchesSearch = fullName.includes(search) || id.includes(search);

        const alreadySelected = form.guarantors.some((g, i) => g.member_id === member.id && i !== index);

        const isApplicant = member.id == form.member_id;

        return matchesSearch && !alreadySelected && !isApplicant;
    });
};

const selectedGuarantorIds = computed(() => form.guarantors.map((g) => g.member_id).filter(Boolean));

const selectedMember = ref(null);
const selectedProduct = ref(null);
const loanCalculation = ref(null);

/* --- NEW: eligibility state and debounce timer --- */
const eligibility = reactive({
    checking: false,
    eligible: true,
    reason: '',
});
let eligibilityDebounceTimer = null;
/* -------------------------------------------------- */

/* Watch relevant fields to recalculate repayment */
watch([() => form.applied_amount, () => form.term_months, () => form.loan_product_id], () => {
    calculateRepayment();
});

/* uploaded files: single combined PDF */
const uploadedFiles = reactive({
    combined: null,
    multiple: [],
});

/* derived */
const isMemberRole = computed(() => props.auth.user.role === 'member');

const memberInfo = computed(() => {
    if (isMemberRole.value) return props.auth.user.member || {};
    return selectedMember.value || {};
});

/* initialize if member */
if (isMemberRole.value && props.auth.user.member) {
    form.member_id = props.auth.user.member.id;
    selectedMember.value = props.auth.user.member;
}

/* helpers */
const onMemberChange = () => {
    selectedMember.value = props.members.find((m) => m.id == form.member_id);

    // RESET DOCUMENTS
    uploadedFiles.multiple = [];
    uploadedFiles.combined = null;
    selectedDocType.value = null;
    if (supportFileInput.value) {
        supportFileInput.value.value = '';
    }

    // (optional but recommended)
    form.guarantors = [];

    triggerEligibilityCheckDebounced();
};

const onProductChange = () => {
    selectedProduct.value = props.loanProducts.find((p) => p.id == form.loan_product_id);
    calculateRepayment();
    triggerEligibilityCheckDebounced();
};

/* --- NEW: watch applied_amount and term to trigger eligibility checks --- */
watch(
    () => form.applied_amount,
    () => {
        triggerEligibilityCheckDebounced();
    },
);
watch(
    () => form.term_months,
    () => {
        triggerEligibilityCheckDebounced();
    },
);
/* --------------------------------------------------------------- */

const calculateRepayment = async () => {
    if (!form.loan_product_id || !form.applied_amount || !form.term_months) {
        loanCalculation.value = null;
        return;
    }

    try {
        const response = await axios.post(route('loan-calculator.calculate'), {
            loan_product_id: form.loan_product_id,
            principal_amount: form.applied_amount,
            term_months: form.term_months,
        });

        const data = response.data.calculation;

        loanCalculation.value = {
            monthlyRepayment: data.loan_details.monthly_payment,
            totalRepayable: data.loan_details.total_repayment,
            processingFee: data.loan_details.processing_fee,
            insuranceFee: data.loan_details.insurance_fee,
            netAmount: data.loan_details.net_disbursement,
            interestAmount: data.loan_details.total_repayment - form.applied_amount,
        };
    } catch (error) {
        console.error('Loan calculation failed:', error);
        loanCalculation.value = null;
    }
};

/* guarantor functions */
const addGuarantor = () => {
    form.guarantors.push({ member_id: '', guaranteed_amount: '' });
};
const removeGuarantor = (index) => form.guarantors.splice(index, 1);

// For dynamic support documents
const showDocTypeSelector = ref(false);
const selectedDocType = ref(null);
const supportFileInput = ref(null);

const docTypes = [
    { name: 'Current Payslip', required: true },
    { name: 'ID Copy', required: false },
    { name: 'Bank Statement', required: false },
    { name: 'Evidence of Business', required: false },
];

const remainingDocTypes = computed(() => docTypes.filter((doc) => !uploadedFiles.multiple.some((f) => f.type === doc.name)));

function chooseDocType(type) {
    selectedDocType.value = type;
    showDocTypeSelector.value = false;
    supportFileInput.value.click(); // trigger file chooser
}

function handleSupportFiles(event) {
    const file = event.target.files[0];
    if (!file) return;
    if (file.type !== 'application/pdf') {
        alert('Only PDF files are allowed.');
        return;
    }

    // prevent duplicates
    if (uploadedFiles.multiple.some((f) => f.type === selectedDocType.value)) {
        alert(`${selectedDocType.value} is already attached.`);
        return;
    }

    uploadedFiles.multiple.push({ type: selectedDocType.value, file });
    selectedDocType.value = null;
    supportFileInput.value.value = ''; // reset input
}

function removeSingleFile(type) {
    uploadedFiles.multiple.splice(0, uploadedFiles.multiple.length, ...uploadedFiles.multiple.filter((f) => f.type !== type));
}

function removeSupportFiles() {
    uploadedFiles.multiple.splice(0);
}

/* human readable filesize */
function humanFileSize(bytes) {
    if (!bytes) return '0 B';
    const thresh = 1024;
    if (Math.abs(bytes) < thresh) return bytes + ' B';
    const units = ['KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
    let u = -1;
    do {
        bytes /= thresh;
        ++u;
    } while (Math.abs(bytes) >= thresh && u < units.length - 1);
    return bytes.toFixed(1) + ' ' + units[u];
}

/* ---  Eligibility check helpers --- */

/**
 * Calls the server endpoint to check eligibility.
 * Returns the server response data or throws.
 */
async function checkEligibilityServer() {
    // Require mandatory fields before sending request
    if (!form.member_id || !form.loan_product_id || !form.applied_amount) {
        return { eligible: true }; // allow UI to remain usable until filled
    }

    // Ensure eligibility object always exists
    if (!eligibility) {
        eligibility = {};
    }

    eligibility.checking = true;

    try {
        const payload = {
            member_id: form.member_id,
            loan_product_id: form.loan_product_id,
            requested_amount: form.applied_amount,
        };

        const response = await axios.post(route('members.loans.check-eligibility'), payload);

        // Safely handle possibly missing data
        const data = response?.data ?? {};
        const respData = data.data ?? {};

        // Extract fields safely
        const eligible = !!respData?.eligible;
        const reason = respData?.messages?.length
            ? respData.messages.join('\n')
            : respData?.reason ||
              respData?.message ||
              (eligible ? 'Member is eligible for this loan product' : 'Member is not eligible for this loan product');

        // Assign the results safely (create missing keys if needed)
        eligibility.eligible = eligible;
        eligibility.reason = reason;
        eligibility.requirements = respData?.requirements || {};
        eligibility.maxLoanAmount = respData?.max_loan_amount || null;

        // Handle frontend validation errors
        if (!eligible) {
            showMessage('error', null, { eligibility: [reason] });
        } else if (errorMessages.value && typeof errorMessages.value === 'object' && errorMessages.value?.eligibility) {
            // Clear old eligibility errors
            const em = { ...errorMessages.value };
            delete em.eligibility;
            errorMessages.value = Object.keys(em).length ? em : null;
        }

        return { eligible, reason, raw: respData };
    } catch (err) {
        console.error('Eligibility check failed:', err.response?.data || err.message);
        eligibility.eligible = false;
        eligibility.reason = 'Unable to verify eligibility at this time.';
        showMessage('error', 'Unable to verify eligibility at this time. Please try again.');
    } finally {
        eligibility.checking = false;
    }
}

/**
 * Debounced trigger for real-time eligibility checks
 */
function triggerEligibilityCheckDebounced() {
    // clear previous timer
    if (eligibilityDebounceTimer) clearTimeout(eligibilityDebounceTimer);

    // if not enough data, skip
    if (!form.member_id || !form.loan_product_id || !form.applied_amount) {
        eligibility.eligible = true;
        eligibility.reason = '';
        return;
    }

    // small debounce to avoid spamming server
    eligibilityDebounceTimer = setTimeout(async () => {
        try {
            await checkEligibilityServer();
        } catch (e) {
            // already handled in checkEligibilityServer (showMessage)
        }
    }, 500);
}

/* Trigger initial check when relevant props change (members/products) */
watch(
    () => form.member_id,
    () => {
        triggerEligibilityCheckDebounced();
    },
);
watch(
    () => form.loan_product_id,
    () => {
        triggerEligibilityCheckDebounced();
    },
);
watch(
    () => form.applied_amount,
    () => {
        triggerEligibilityCheckDebounced();
    },
);

const hasSelfGuarantor = computed(() => {
    return form.guarantors.some((g) => g.member_id == form.member_id);
});

const hasMandatoryPayslip = computed(() => uploadedFiles.multiple.some((f) => f.type === 'Current Payslip'));

const hasGuarantor = computed(() => form.guarantors.some((g) => g.member_id));

/* --------------------------------------------------------------- */

/* submit controls - updated to include eligibility check */
const canSubmit = computed(() => {
    const hasBasicInfo = !!form.member_id && !!form.loan_product_id && !!form.applied_amount && !!form.term_months && !!form.purpose;

    const hasDisbursement =
        !!disbursementMethod.value &&
        ((disbursementMethod.value === 'mpesa' && !!disbursementDetails.mpesaNumber) ||
            (disbursementMethod.value === 'bank' &&
                !!disbursementDetails.bankName &&
                !!disbursementDetails.branch &&
                !!disbursementDetails.accountNumber) ||
            (disbursementMethod.value === 'cheque' && !!disbursementDetails.payee));

    const hasDocuments = uploadedFiles.combined !== null || (Array.isArray(uploadedFiles.multiple) && uploadedFiles.multiple.length > 0);

    // Also require that eligibility has been checked and is true.
    // If eligibility.checking is true, we conservatively disallow submit (to avoid race).
    const eligibilityOk = eligibility.eligible === true && !eligibility.checking;

    return (
        hasBasicInfo && hasDisbursement && hasDocuments && eligibilityOk && hasGuarantor.value && hasMandatoryPayslip.value && !hasSelfGuarantor.value
    );
});

/* feedback helper */
const showMessage = (type, message, errors = null, callback = null) => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
    if (type === 'success') {
        successMessage.value = message;
        errorMessages.value = null;
    } else {
        errorMessages.value = errors || { general: [message] };
        successMessage.value = null;
    }
    setTimeout(() => {
        successMessage.value = null;
        errorMessages.value = null;
        if (callback) callback();
    }, 8000);
};

/* reset form */
const resetForm = () => {
    form.member_id = isMemberRole.value ? props.auth.user.member?.id || '' : '';
    form.loan_product_id = '';
    form.applied_amount = '';
    form.term_months = '';
    form.purpose = '';
    form.guarantors = [];
    uploadedFiles.combined = null;
    selectedMember.value = isMemberRole.value ? props.auth.user.member : null;
    selectedProduct.value = null;
    loanCalculation.value = null;
    disbursementMethod.value = '';
    Object.assign(disbursementDetails, { mpesaNumber: '', bankName: '', branch: '', accountNumber: '', payee: '' });

    // reset eligibility
    eligibility.checking = false;
    eligibility.eligible = true;
    eligibility.reason = '';
};

/* submit flow (confirm -> send) */
const submitApplication = () => {
    // attempt to run a final eligibility check before showing confirm modal.
    // If eligibility fails, show error and don't open confirm modal.
    // If check throws network error, we block submission and show message.
    if (!hasGuarantor.value) {
        showMessage('error', 'Please add at least one guarantor before submitting.');
        return;
    }

    if (!hasMandatoryPayslip.value) {
        showMessage('error', null, {
            general: ['Current Payslip is mandatory.'],
        });
        return;
    }

    if (hasSelfGuarantor.value) {
        showMessage('error', 'Applicant cannot be their own guarantor.');
        return;
    }
    if (!hasMandatoryPayslip.value) {
        errorMessages.value = {
            general: ['Current Payslip is mandatory.'],
        };
        return;
    }

    const isSepuStaff = memberInfo.value?.employer === 'SEPU';

    if (!isSepuStaff) {
        const hasBankStatement = uploadedFiles.multiple.some((f) => f.type === 'Bank Statement');

        const hasBusinessEvidence = uploadedFiles.multiple.some((f) => f.type === 'Evidence of Business');

        if (!hasBankStatement && !hasBusinessEvidence) {
            showMessage('error', null, {
                general: ['Non-SEPU staff must upload either a Bank Statement or Evidence of Business.'],
            });
            return;
        }
    }

    showConfirm.value = false;
    processing.value = true;
    checkEligibilityServer()
        .then((result) => {
            if (!result.eligible) {
                // show message and prevent opening confirm
                showMessage('error', null, { eligibility: [result.reason || 'Member not eligible for this loan'] });
            } else {
                // open confirmation modal when eligible
                showConfirm.value = true;
            }
        })
        .catch(() => {
            // checkEligibilityServer already called showMessage; keep confirm closed
        })
        .finally(() => {
            processing.value = false;
        });
};

const confirmSubmit = async () => {
    showConfirm.value = false;
    processing.value = true;

    // Clean guarantors
    form.guarantors = form.guarantors.filter((g) => g.member_id && g.guaranteed_amount);

    try {
        const formData = new FormData();

        // Basic fields
        formData.append('member_id', form.member_id);
        formData.append('loan_product_id', form.loan_product_id);
        formData.append('applied_amount', form.applied_amount);
        formData.append('term_months', form.term_months);
        formData.append('purpose', form.purpose);

        // Disbursement
        formData.append('disbursement_method', disbursementMethod.value);

        if (disbursementMethod.value === 'mpesa') {
            formData.append('mpesa_number', disbursementDetails.mpesaNumber);
        }

        if (disbursementMethod.value === 'bank') {
            formData.append('bank_name', disbursementDetails.bankName);
            formData.append('bank_branch', disbursementDetails.branch);
            formData.append('bank_account', disbursementDetails.accountNumber);
        }

        if (disbursementMethod.value === 'cheque') {
            formData.append('payee', disbursementDetails.payee);
        }

        // Guarantors
        form.guarantors.forEach((g, index) => {
            formData.append(`guarantors[${index}][member_id]`, g.member_id);
            formData.append(`guarantors[${index}][guaranteed_amount]`, g.guaranteed_amount);
        });

        // Files
        uploadedFiles.multiple.forEach((fileObj, index) => {
            formData.append(`documents[${index}]`, fileObj.file);
            formData.append(`document_types[${index}]`, fileObj.type);
        });

        // Send request
        const response = await axios.post(route('loans.store'), formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });

        if (response.data?.success) {
            resetForm();

            showMessage('success', response.data.message, null, () => {
                router.visit(isMemberRole.value ? route('my-loans') : route('loans.index'));
            });
        } else {
            showMessage('error', response.data?.message || 'Unexpected response from server.');
        }
    } catch (error) {
        if (error.response?.status === 422) {
            const data = error.response.data;

            if (data.errors) {
                showMessage('error', null, data.errors);
            } else if (data.message) {
                showMessage('error', data.message);
            }
        } else {
            console.error('Unexpected error:', error);
            showMessage('error', 'Something went wrong. Please try again.');
        }
    } finally {
        processing.value = false;
    }
};

// eligibility state
const isEligible = ref(true);
const ineligibleReasons = ref([]);

/**
 * Checks loan eligibility
 */
const checkEligibility = async () => {
    // Assign fallback values if not provided
    const memberId = form.member_id;
    const productId = form.loan_product_id || 1; // fallback to first product ID or 1
    const amount = form.applied_amount || 1; // fallback to 0

    if (!memberId) {
        console.log('No member ID available for eligibility check');
        return;
    }

    try {
        const payload = {
            member_id: memberId,
            loan_product_id: productId,
            requested_amount: amount,
        };

        const response = await axios.post(route('members.loans.check-eligibility'), payload);

        const data = response.data.data;

        isEligible.value = data.eligible;
        ineligibleReasons.value = data.reasons || data.messages || [];
    } catch (error) {
        console.error('Eligibility check failed:', error);
        isEligible.value = false;
        ineligibleReasons.value = ['Unable to verify eligibility. Please try again later.'];
    }
};

// Run automatically for member role
onMounted(() => {
    if (isMemberRole.value && props.auth?.user?.member?.id) {
        form.member_id = props.auth.user.member.id;
        selectedMember.value = props.auth.user.member;
        checkEligibility();
    }
});

// Run automatically for admin when selecting a member
watch(
    () => form.member_id,
    (newVal) => {
        if (!isMemberRole.value && newVal) {
            checkEligibility();
        }
    },
);

watchEffect(() => {
    if (!isEligible.value && (errorMessages.value || successMessage.value)) {
        setTimeout(() => {
            errorMessages.value = null;
            successMessage.value = null;
        }, 200);
    }
});

const resetMemberSelection = () => {
    form.member_id = '';
    selectedMember.value = null;
    isEligible.value = true;
    resetForm();
};
</script>

<style scoped>
/* small extras to enhance the modern feel (feel free to move into your global styles) */
input[type='file'] {
    display: none;
}

.rounded-2xl {
    border-radius: 1rem;
}
</style>
