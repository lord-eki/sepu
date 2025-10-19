<template>
  <AppLayout :breadcrumbs="[
    { title: 'Loans', href: isMemberRole ? route('my-loans') : route('loans.index') },
    { title: 'Apply' }
  ]">

    <Head title="New Loan Application" />
    <!-- Flash messages -->
    <div class="">
      <transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 -translate-y-2"
        enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-2">
        <div v-if="successMessage || errorMessages" :class="[
    successMessage ? 'bg-emerald-100 text-emerald-900 border-emerald-300' : 'bg-rose-100 text-rose-900 border-rose-300',
    'relative w-fit mx-auto px-6 py-3 rounded-xl mb-4 flex items-start gap-4 shadow-md'
  ]">
          <div class="flex-1">
            <p v-if="successMessage">{{ successMessage }}</p>
            <ul v-else class="list-disc pl-5 space-y-1">
              <li v-for="(errs, field) in errorMessages" :key="field">
                <span v-if="field !== 'general'"><strong class="capitalize">{{ field }}:</strong> </span>
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
          <button type="button" class="ml-2 text-gray-400 hover:text-gray-200"
            @click="() => { successMessage = null; errorMessages = null }">X</button>
        </div>
      </transition>
    </div>


    <!-- Dark modern background -->
    <div
      class="min-h-screen py-8 px-4 sm:px-6 lg:px-8 bg-gradient-to-b from-blue-50 via-white to-orange-50 text-gray-800">

      <div class="max-w-4xl mx-auto">

        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
          <div>
            <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight text-[rgb(7,40,75)]">New Loan Application</h1>
            <p v-if="isEligible" class="mt-1 text-sm text-blue-900">Complete this form to apply for a loan.</p>
            <p v-else class="mt-1 text-sm text-blue-900">To apply loan you must meet all the requirements.</p>
          </div>

          <Link :href="isMemberRole ? route('my-loans') : route('loans.index')"
            class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-xl shadow-lg text-sm font-medium transition">
          <ArrowLeft class="w-4 h-4" />
          <p>Back&nbsp;<span class="max-sm:hidden">to Loans</span></p>
          </Link>
        </div>

        <!-- Eligibility Check Message -->
        <div v-if="!isEligible" class="bg-rose-50 border border-rose-200 text-rose-800 rounded-xl p-6 mb-8 shadow">
          <h3 class="font-semibold text-lg mb-2">
            <template v-if="isMemberRole">
              You are currently not eligible for a loan.
            </template>
            <template v-else>
              Loan Application Not Allowed
            </template>
          </h3>

          <p class="text-sm mb-3">
            <template v-if="isMemberRole">
              Reason(s):
            </template>
            <template v-else>
              The selected member with membership ID
              <strong>{{ memberInfo.membership_id }}</strong>
              is currently <strong>not eligible</strong> for a loan. Reason(s):
            </template>
          </p>

          <ul class="list-disc pl-6 text-sm space-y-1 mb-4">
            <li v-for="(reason, i) in ineligibleReasons" :key="i">{{ reason }}</li>
          </ul>

          <!-- Only show back button for non-member users -->
          <div v-if="!isMemberRole" class="flex justify-end">
            <button type="button" @click="resetMemberSelection"
              class="inline-flex items-center gap-2 bg-[rgba(7,40,75,0.95)] hover:bg-[rgba(7,40,75,0.85)] text-white px-4 py-2 rounded-lg shadow text-sm transition">
              <ArrowLeft class="w-4 h-4" />
              <span>Go Back to Member Selection</span>
            </button>
          </div>
        </div>



        <!-- Loan Application Form -->
        <form v-else @submit.prevent="submitApplication" class="space-y-8">


          <!-- Member Information (card) -->
          <section class="bg-white text-gray-900 shadow-xl rounded-2xl overflow-hidden border border-gray-100">
            <div class="px-6 py-3 bg-[rgba(7,40,75,0.95)]">
              <h3 class="text-base sm:text-lg font-semibold text-white">Member Information</h3>
            </div>
            <div class="p-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div v-if="!isMemberRole">
                  <label for="member_id" class="block text-sm font-medium text-gray-700">Select Member *</label>
                  <select v-model="form.member_id" @change="onMemberChange" id="member_id" required
                    class="mt-2 block w-full rounded-lg border border-gray-300 p-3 focus:outline-none focus:ring-2 focus:ring-orange-400">
                    <option value="">Choose a member...</option>
                    <option v-for="member in members" :key="member.id" :value="member.id">
                      {{ member.first_name }} {{ member.last_name }} - {{ member.membership_id }}
                    </option>
                  </select>
                </div>

                <div v-if="selectedMember || isMemberRole">
                  <label class="block text-sm font-medium text-gray-700">Member Details</label>
                  <div class="mt-2 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <p class="text-sm font-semibold text-gray-900">{{ memberInfo.first_name }} {{ memberInfo.last_name
                      }}</p>
                    <p class="text-sm text-gray-500">{{ memberInfo.membership_id }}</p>
                    <p class="text-sm text-gray-500">Phone: {{ auth.user.phone }}</p>
                  </div>
                </div>
              </div>
            </div>
          </section>

          <!-- Loan Product -->
          <section class="bg-white text-gray-900 shadow-xl rounded-2xl border border-gray-100 overflow-hidden">
            <div class="px-6 py-3 bg-[rgba(7,40,75,0.95)]">
              <h3 class="text-base sm:text-lg font-semibold text-white">Loan Product</h3>
            </div>
            <div class="p-6">
              <label for="loan_product_id" class="block text-sm font-medium text-gray-700">Select Loan Product *</label>
              <select v-model="form.loan_product_id" @change="onProductChange" id="loan_product_id" required
                class="mt-2 block w-full rounded-lg border border-gray-300 p-3 focus:outline-none focus:ring-2 focus:ring-orange-400">
                <option value="">Choose a loan product...</option>
                <option v-for="product in loanProducts" :key="product.id" :value="product.id">
                  {{ product.name }} - {{ product.interest_rate }}% p.a.
                </option>
              </select>

              <div v-if="selectedProduct"
                class="mt-4 bg-[rgba(232,240,255,0.95)] border border-blue-100 p-4 rounded-lg">
                <h4 class="font-semibold text-blue-900 mb-2">Product Details</h4>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                  <div><span class="text-blue-700">Interest Rate:</span>
                    <p class="font-medium">{{ selectedProduct.interest_rate }}% p.a.</p>
                  </div>
                  <div><span class="text-blue-700">Amount Range:</span>
                    <p class="font-medium">KES {{ formatCurrency(selectedProduct.min_amount) }} - {{
    formatCurrency(selectedProduct.max_amount) }}</p>
                  </div>
                  <div><span class="text-blue-700">Term Range:</span>
                    <p class="font-medium">{{ selectedProduct.min_term_months }} - {{ selectedProduct.max_term_months }}
                      months
                    </p>
                  </div>
                  <div><span class="text-blue-700">Processing Fee:</span>
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
          <section class="bg-white text-gray-900 shadow-xl rounded-2xl border border-gray-100 overflow-hidden">
            <div class="px-6 py-3 bg-[rgba(7,40,75,0.95)]">
              <h3 class="text-base sm:text-lg font-semibold text-white">Loan Details</h3>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label for="amount" class="block text-sm font-medium text-gray-700">Loan Amount *</label>
                <input v-model="form.applied_amount" type="number" id="amount" required
                  class="mt-2 block w-full rounded-lg border border-gray-300 p-3 focus:outline-none focus:ring-2 focus:ring-orange-400" />
              </div>
              <div>
                <label for="term" class="block text-sm font-medium text-gray-700">Repayment Term (months) *</label>
                <input v-model="form.term_months" type="number" id="term" required
                  class="mt-2 block w-full rounded-lg border border-gray-300 p-3 focus:outline-none focus:ring-2 focus:ring-orange-400" />
              </div>

              <div class="md:col-span-2">
                <label for="purpose" class="block text-sm font-medium text-gray-700">Purpose *</label>
                <textarea v-model="form.purpose" id="purpose" rows="3" required
                  class="mt-2 block w-full rounded-lg border border-gray-300 p-3 focus:outline-none focus:ring-2 focus:ring-orange-400"></textarea>
              </div>

              <!-- Repayment preview (if available) -->
              <div v-if="loanCalculation" class="md:col-span-2 mt-2 bg-gray-50 rounded-lg p-4 border border-gray-200">
                <h4 class="font-medium text-sm text-gray-800">Repayment estimate</h4>
                <div class="mt-2 text-sm text-gray-700">
                  <div>Monthly Repayment: KES {{ formatCurrency(loanCalculation.monthlyRepayment.toFixed(2)) }}</div>
                  <div>Total Repayable: KES {{ formatCurrency(loanCalculation.totalRepayable.toFixed(2)) }}</div>
                  <div>Processing Fee: KES {{ formatCurrency(loanCalculation.processingFee.toFixed(2)) }}</div>
                  <div>Insurance Fee: KES {{ formatCurrency(loanCalculation.insuranceFee.toFixed(2)) }}</div>
                  <div>Net Amount (after fees): KES {{ formatCurrency(loanCalculation.netAmount.toFixed(2)) }}</div>
                </div>
              </div>
            </div>
          </section>

          <!-- Disbursement -->
          <section class="bg-white text-gray-900 shadow-xl rounded-2xl border border-gray-100 overflow-hidden">
            <div class="px-6 py-3 bg-[rgba(7,40,75,0.95)]">
              <h3 class="text-base sm:text-lg font-semibold text-white">Disbursement Method</h3>
            </div>
            <div class="p-6 space-y-4">
              <div>
                <label for="disbursement" class="block text-sm font-medium text-gray-700">Choose Method *</label>
                <select id="disbursement" v-model="disbursementMethod" required
                  class="mt-2 block w-full rounded-lg border border-gray-300 p-3 focus:outline-none focus:ring-2 focus:ring-orange-400">
                  <option value="">-- Select Method --</option>
                  <option value="mpesa">M-Pesa</option>
                  <option value="bank">Bank</option>
                  <option value="cheque">Cheque</option>
                </select>
              </div>

              <div v-if="disbursementMethod === 'mpesa'">
                <label for="mpesaNumber" class="block text-sm font-medium text-gray-700">M-Pesa Number *</label>
                <input id="mpesaNumber" type="text" v-model="disbursementDetails.mpesaNumber"
                  placeholder="e.g. 0712345678" required
                  class="mt-2 block w-full rounded-lg border border-gray-300 p-3 focus:outline-none focus:ring-2 focus:ring-orange-400" />
              </div>

              <div v-if="disbursementMethod === 'bank'">
                <label for="bankName" class="block text-sm font-medium text-gray-700">Bank Name *</label>
                <input id="bankName" type="text" v-model="disbursementDetails.bankName" placeholder="e.g. Equity Bank"
                  required
                  class="mt-2 block w-full rounded-lg border border-gray-300 p-3 focus:outline-none focus:ring-2 focus:ring-orange-400" />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                  <div>
                    <label for="branch" class="block text-sm font-medium text-gray-700">Branch *</label>
                    <input id="branch" type="text" v-model="disbursementDetails.branch" placeholder="e.g. Nairobi CBD"
                      required
                      class="mt-2 block w-full rounded-lg border border-gray-300 p-3 focus:outline-none focus:ring-2 focus:ring-orange-400" />
                  </div>
                  <div>
                    <label for="accountNumber" class="block text-sm font-medium text-gray-700">Account Number *</label>
                    <input id="accountNumber" type="text" v-model="disbursementDetails.accountNumber"
                      placeholder="e.g. 123456789" required
                      class="mt-2 block w-full rounded-lg border border-gray-300 p-3 focus:outline-none focus:ring-2 focus:ring-orange-400" />
                  </div>
                </div>
              </div>

              <div v-if="disbursementMethod === 'cheque'">
                <label for="payee" class="block text-sm font-medium text-gray-700">Payee Name *</label>
                <input id="payee" type="text" v-model="disbursementDetails.payee" placeholder="Enter Payee Name"
                  required
                  class="mt-2 block w-full rounded-lg border border-gray-300 p-3 focus:outline-none focus:ring-2 focus:ring-orange-400" />
              </div>
            </div>
          </section>

          <!-- Guarantors -->
          <section class="bg-white text-gray-900 shadow-xl rounded-2xl border border-gray-100 overflow-hidden">
            <div class="px-6 py-3 bg-[rgba(7,40,75,0.95)]">
              <h3 class="text-base sm:text-lg font-semibold text-white">Guarantors</h3>
            </div>
            <div class="p-6 space-y-4">
              <p class="text-sm text-gray-600 mb-2">
                Select guarantors from members. <span class="font-medium text-blue-900">Selected guarantors will be
                  notified.</span>
              </p>

              <div v-for="(guarantor, index) in form.guarantors" :key="index" class="p-4 border rounded-lg space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Select Member *</label>
                    <select v-model="guarantor.member_id"
                      class="mt-2 block w-full rounded-lg border border-gray-300 p-3">
                      <option value="">Choose a member...</option>
                      <option v-for="member in members" :key="member.id" :value="member.id">
                        {{ member.first_name }} {{ member.last_name }} - {{ member.membership_id }}
                      </option>
                    </select>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700">Guaranteed Amount (KES) *</label>
                    <input v-model="guarantor.guaranteed_amount" type="number" step="0.01" min="0"
                      :max="form.applied_amount" class="mt-2 block w-full rounded-lg border border-gray-300 p-3" />
                  </div>
                </div>

                <div class="flex justify-end">
                  <button type="button" @click="removeGuarantor(index)"
                    class="text-rose-600 hover:text-rose-800 text-sm font-medium">
                    ✕ Remove Guarantor
                  </button>
                </div>
              </div>

              <div>
                <button type="button" @click="addGuarantor"
                  class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white px-3 py-2 rounded-lg shadow">
                  + Add Guarantor
                </button>
              </div>
            </div>
          </section>

          <!-- Support Documents -->
          <section class="bg-white text-gray-900 shadow-xl rounded-2xl border border-gray-100 overflow-hidden">
            <div class="px-6 py-3 bg-[rgba(7,40,75,0.95)]">
              <h3 class="text-base sm:text-lg font-semibold text-white">Support Documents</h3>
            </div>
            <div class="p-6 space-y-4">
              <!-- Instructions -->
              <div class="bg-blue-50 border border-blue-100 p-4 rounded-lg text-sm text-blue-900">
                <strong>Important:</strong>
                <ul class="list-disc pl-6 mt-1 space-y-1">
                  <li> Click 'Choose Document' button to select each document separately (Payslip, ID, Bank Statement,
                    Employment Letter, Guarantor Form).</li>
                </ul>
              </div>

              <!-- File Upload Control -->
              <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                <button type="button" @click="showDocTypeSelector = true"
                  class="inline-flex items-center gap-2 bg-[rgba(7,40,75,0.95)] hover:bg-[rgba(7,40,75,0.85)] text-white px-4 py-2 w-fit rounded-lg shadow">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  </svg>
                  Choose Document
                </button>

                <!-- Selected Files Display -->
                <div class="flex-1 min-w-0">
                  <template v-if="uploadedFiles.multiple.length">
                    <strong class="block mb-1 text-sm">Uploaded Documents:</strong>
                    <ul class="list-disc pl-5 text-sm space-y-1">
                      <li v-for="(file, index) in uploadedFiles.multiple" :key="file.type"
                        class="flex justify-between items-center">
                        <div>
                          <span class="font-medium">{{ file.type }}</span> —
                          {{ file.file.name }}
                          <span class="text-xs text-gray-500">({{ humanFileSize(file.file.size) }})</span>
                        </div>
                        <button type="button" @click="removeSingleFile(file.type)"
                          class="text-xs text-rose-600 hover:text-rose-800">
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
                  <button type="button" @click="removeSupportFiles" class="text-sm text-rose-600 hover:text-rose-800">
                    Remove All
                  </button>
                </div>
              </div>

              <!-- Modal: Choose Document Type -->
              <div v-if="showDocTypeSelector"
                class="fixed inset-0 bg-black/70 z-50 flex items-center justify-center p-4">
                <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md">
                  <h4 class="text-lg font-semibold mb-4">Select Document Type</h4>
                  <div class="space-y-3">
                    <button v-for="type in remainingDocTypes" :key="type" @click="chooseDocType(type)"
                      class="w-full text-left px-4 py-2 hover:cursor-pointer border rounded-lg hover:bg-blue-50 transition">
                      {{ type }}
                    </button>
                  </div>
                  <div class="flex justify-end mt-4">
                    <button @click="showDocTypeSelector = false"
                      class="text-gray-600 hover:cursor-pointer bg-slate-300 hover:bg-slate-200 rounded-md py-2 px-3 text-sm hover:text-gray-800">Cancel</button>
                  </div>
                </div>
              </div>

              <!-- Hidden File Input -->
              <input ref="supportFileInput" type="file" accept="application/pdf" class="hidden"
                @change="handleSupportFiles" />

              <p class="text-xs text-gray-400 mt-2">Only PDF files allowed. Maximum 10MB each.</p>
            </div>
          </section>



          <!-- Submit row -->
          <div class="flex justify-end items-center gap-4">
            <Link :href="isMemberRole ? route('my-loans') : route('loans.index')"
              class="px-5 py-3 rounded-lg bg-gray-100 text-gray-800 hover:bg-gray-200 shadow">
            Cancel
            </Link>
            <button type="submit" :disabled="!canSubmit || processing"
              class="inline-flex items-center gap-3 bg-orange-500 hover:bg-orange-600 hover:cursor-pointer disabled:bg-gray-400 text-white px-6 py-3 rounded-xl font-medium shadow">
              <svg v-if="processing" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
                fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
              </svg>
              <span v-if="processing">Submitting...</span>
              <span v-else>Submit Application</span>
            </button>
          </div>
        </form>

        <!-- Confirmation modal -->
        <div v-if="showConfirm" class="fixed inset-0 z-50 flex items-center justify-center bg-[rgba(0,0,0,0.7)] p-4">
          <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full p-6 overflow-y-auto max-h-[90vh]">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Review Your Loan Application</h3>

            <div class="space-y-3 text-sm text-gray-700">
              <div><span class="font-medium text-gray-900">Member:</span> {{ memberInfo.first_name }} {{
    memberInfo.last_name }}
                <span class="text-xs text-gray-500">({{ memberInfo.membership_id }})</span>
              </div>
              <div><span class="font-medium text-gray-900">Loan Product:</span> {{ loanProducts.find(p => p.id ===
    form.loan_product_id)?.name }}</div>
              <div><span class="font-medium text-gray-900">Applied Amount:</span> KES {{ form.applied_amount }}</div>
              <div><span class="font-medium text-gray-900">Term:</span> {{ form.term_months }} months</div>
              <div><span class="font-medium text-gray-900">Purpose:</span> {{ form.purpose }}</div>

              <div v-if="form.guarantors.length">
                <span class="font-medium text-gray-900">Guarantors:</span>
                <ul class="list-disc ml-6 mt-1">
                  <li v-for="(g, index) in form.guarantors" :key="index">
                    {{ members.find(m => m.id === g.member_id)?.first_name }} {{ members.find(m => m.id ===
    g.member_id)?.last_name }} — Guaranteed: KES {{ g.guaranteed_amount }}
                  </li>
                </ul>
              </div>

              <div>
                <span class="font-medium text-gray-900">Disbursement Method:</span> {{ disbursementMethod }}
                <div class="ml-4 mt-1 text-sm">
                  <div v-if="disbursementMethod === 'mpesa'">M-Pesa Number: {{ disbursementDetails.mpesaNumber }}</div>
                  <div v-if="disbursementMethod === 'bank'">Bank: {{ disbursementDetails.bankName }}, Branch: {{
    disbursementDetails.branch }}, Account: {{ disbursementDetails.accountNumber }}</div>
                  <div v-if="disbursementMethod === 'cheque'">Payee: {{ disbursementDetails.payee }}</div>
                </div>
              </div>

              <div>
                <span class="font-medium text-gray-900">Attached Documents:</span>

                <div class="ml-4 mt-2 space-y-1 text-sm">
                  <!-- Combined PDF (if uploaded) -->
                  <div v-if="uploadedFiles.combined">
                    <svg class="w-4 h-4 text-blue-600 inline-block mr-1" fill="none" stroke="currentColor"
                      stroke-width="2" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    <span class="text-gray-800">
                      {{ uploadedFiles.combined.name || 'Combined Documents.pdf' }}
                      <span class="text-xs text-gray-500">
                        ({{ humanFileSize(uploadedFiles.combined.size || 0) }})
                      </span>
                    </span>
                  </div>

                  <!-- Individual Files -->
                  <div v-else-if="uploadedFiles.multiple?.length">
                    <ul class="list-disc pl-5 space-y-1 text-gray-800">
                      <li v-for="(fileObj, index) in uploadedFiles.multiple" :key="index"
                        class="flex items-center justify-between">
                        <span>
                          {{ fileObj.type || fileObj.file?.name }}
                          <span class="text-xs text-gray-500">
                            ({{ humanFileSize(fileObj.file?.size || 0) }})
                          </span>
                        </span>
                      </li>
                    </ul>
                  </div>

                  <!-- None -->
                  <div v-else class="italic text-gray-400">
                    No documents uploaded
                  </div>
                </div>
              </div>


            </div>

            <div class="flex justify-end gap-3 mt-6">
              <button type="button" @click="showConfirm = false"
                class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:cursor-pointer hover:bg-gray-100">Cancel</button>
              <button type="button" @click="confirmSubmit"
                class="px-4 py-2 rounded-lg bg-[rgba(7,40,75,0.95)] text-white hover:cursor-pointer hover:bg-blue-800 shadow">Yes,
                Submit</button>
            </div>
          </div>
        </div>

        <!-- Fullscreen loader (subtle) -->
        <div v-if="processing" class="fixed inset-0 z-50 flex items-center justify-center bg-[rgba(0,0,0,0.6)]">
          <div class="bg-white px-6 py-4 rounded-lg shadow-lg flex items-center gap-3">
            <svg class="animate-spin h-6 w-6 text-blue-700" xmlns="http://www.w3.org/2000/svg" fill="none"
              viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
            </svg>
            <span class="text-gray-900 font-medium">Submitting your loan application...</span>
          </div>
        </div>

      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive, onMounted, computed, watch, watchEffect } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import axios from 'axios'

/* props from server */
const props = defineProps({
  loanProducts: Array,
  members: Array,
  auth: Object
})

/* state */
const successMessage = ref(null)
const errorMessages = ref(null)
const processing = ref(false)
const showConfirm = ref(false)
import { ArrowLeft } from 'lucide-vue-next'


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
  mpesa_number: ''
})




function formatCurrency(value) {
  if (value == null || value === '' || isNaN(value)) return '0.00'
  return new Intl.NumberFormat('en-KE', {
    style: 'decimal',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(value)
}

const disbursementMethod = ref('')
const disbursementDetails = reactive({
  mpesaNumber: '',
  bankName: '',
  branch: '',
  accountNumber: '',
  payee: ''
})

const selectedMember = ref(null)
const selectedProduct = ref(null)
const loanCalculation = ref(null)

/* --- NEW: eligibility state and debounce timer --- */
const eligibility = reactive({
  checking: false,
  eligible: true,
  reason: ''
})
let eligibilityDebounceTimer = null
/* -------------------------------------------------- */

/* Watch relevant fields to recalculate repayment */
watch(
  [() => form.applied_amount, () => form.term_months, selectedProduct],
  ([amount, term, product]) => {
    if (product && amount && term) {
      calculateRepayment(product, amount, term)
    } else {
      loanCalculation.value = null
    }
  }
)


/* uploaded files: single combined PDF */
const uploadedFiles = reactive({
  combined: null,
  multiple: []
})


/* derived */
const isMemberRole = computed(() => props.auth.user.role === 'member')

const memberInfo = computed(() => {
  if (isMemberRole.value) return props.auth.user.member || {}
  return selectedMember.value || {}
})

/* initialize if member */
if (isMemberRole.value && props.auth.user.member) {
  form.member_id = props.auth.user.member.id
  selectedMember.value = props.auth.user.member
}

/* helpers */
const onMemberChange = () => {
  selectedMember.value = props.members.find(m => m.id == form.member_id)
  triggerEligibilityCheckDebounced()
}

const onProductChange = () => {
  selectedProduct.value = props.loanProducts.find(p => p.id == form.loan_product_id)
  calculateRepayment()
  triggerEligibilityCheckDebounced()
}

/* --- NEW: watch applied_amount and term to trigger eligibility checks --- */
watch(() => form.applied_amount, () => {
  triggerEligibilityCheckDebounced()
})
watch(() => form.term_months, () => {
  triggerEligibilityCheckDebounced()
})
/* --------------------------------------------------------------- */

const calculateRepayment = (product, amount, term) => {
  // keep backward compatibility if function is called with different args
  let prod = product || selectedProduct.value
  let applied = amount ?? form.applied_amount
  let months = term ?? form.term_months

  if (!prod || !applied || !months) {
    loanCalculation.value = null
    return
  }

  const principal = parseFloat(applied)
  const monthlyRate = prod.interest_rate / 100 / 12
  const monthsInt = parseInt(months)

  let monthlyRepayment
  if (monthlyRate === 0) {
    monthlyRepayment = principal / monthsInt
  } else {
    monthlyRepayment = principal * (monthlyRate * Math.pow(1 + monthlyRate, monthsInt)) / (Math.pow(1 + monthlyRate, monthsInt) - 1)
  }

  const totalRepayable = monthlyRepayment * monthsInt
  const processingFee = principal * (prod.processing_fee_rate / 100)
  const insuranceFee = principal * (prod.insurance_rate / 100)
  const netAmount = principal - processingFee - insuranceFee

  loanCalculation.value = {
    monthlyRepayment,
    totalRepayable,
    processingFee,
    insuranceFee,
    netAmount
  }
}

/* guarantor functions */
const addGuarantor = () => {
  form.guarantors.push({ member_id: '', guaranteed_amount: '' })
}
const removeGuarantor = (index) => form.guarantors.splice(index, 1)



// For dynamic support documents
const showDocTypeSelector = ref(false)
const selectedDocType = ref(null)
const supportFileInput = ref(null)

const docTypes = ['Payslip', 'ID Copy', 'Bank Statement', 'Employment Letter', 'Guarantor Form']

const remainingDocTypes = computed(() =>
  docTypes.filter(type => !uploadedFiles.multiple.some(f => f.type === type))
)

function chooseDocType(type) {
  selectedDocType.value = type
  showDocTypeSelector.value = false
  supportFileInput.value.click() // trigger file chooser
}

function handleSupportFiles(event) {
  const file = event.target.files[0]
  if (!file) return
  if (file.type !== 'application/pdf') {
    alert('Only PDF files are allowed.')
    return
  }

  // prevent duplicates
  if (uploadedFiles.multiple.some(f => f.type === selectedDocType.value)) {
    alert(`${selectedDocType.value} is already attached.`)
    return
  }

  uploadedFiles.multiple.push({ type: selectedDocType.value, file })
  selectedDocType.value = null
  supportFileInput.value.value = '' // reset input
}

function removeSingleFile(type) {
  uploadedFiles.multiple = uploadedFiles.multiple.filter(f => f.type !== type)
}

function removeSupportFiles() {
  uploadedFiles.multiple = []
}


/* human readable filesize */
function humanFileSize(bytes) {
  if (!bytes) return '0 B'
  const thresh = 1024
  if (Math.abs(bytes) < thresh) return bytes + ' B'
  const units = ['KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB']
  let u = -1
  do {
    bytes /= thresh
    ++u
  } while (Math.abs(bytes) >= thresh && u < units.length - 1)
  return bytes.toFixed(1) + ' ' + units[u]
}

/* ---  Eligibility check helpers --- */

/**
 * Calls the server endpoint to check eligibility.
 * Returns the server response data or throws.
 */
async function checkEligibilityServer() {
  // Require mandatory fields before sending request
  if (!form.member_id || !form.loan_product_id || !form.applied_amount) {
    return { eligible: true } // allow UI to remain usable until filled
  }

  // Ensure eligibility object always exists
  if (!eligibility) {
    eligibility = {}
  }

  eligibility.checking = true

  try {
    const payload = {
      member_id: form.member_id,
      loan_product_id: form.loan_product_id,
      requested_amount: form.applied_amount,
    }

    const response = await axios.post(route('members.loans.check-eligibility'), payload)

    // Safely handle possibly missing data
    const data = response?.data ?? {}
    const respData = data.data ?? {}

    // Extract fields safely
    const eligible = !!respData?.eligible
    const reason =
      respData?.messages?.length
        ? respData.messages.join('\n')
        : (
          respData?.reason ||
          respData?.message ||
          (eligible
            ? 'Member is eligible for this loan product'
            : 'Member is not eligible for this loan product')
        )

    // Assign the results safely (create missing keys if needed)
    eligibility.eligible = eligible
    eligibility.reason = reason
    eligibility.requirements = respData?.requirements || {}
    eligibility.maxLoanAmount = respData?.max_loan_amount || null

    // Handle frontend validation errors
    if (!eligible) {
      showMessage('error', null, { eligibility: [reason] })
    } else if (errorMessages && typeof errorMessages.value === 'object' && errorMessages.value?.eligibility) {
      // Clear old eligibility errors
      const em = { ...errorMessages.value }
      delete em.eligibility
      errorMessages.value = Object.keys(em).length ? em : null
    }

    return { eligible, reason, raw: respData }

  } catch (err) {
    console.error('Eligibility check failed:', err.response?.data || err.message)
    eligibility.eligible = false
    eligibility.reason = 'Unable to verify eligibility at this time.'
    showMessage('error', 'Unable to verify eligibility at this time. Please try again.')
  } finally {
    eligibility.checking = false
  }
}



/**
 * Debounced trigger for real-time eligibility checks
 */
function triggerEligibilityCheckDebounced() {
  // clear previous timer
  if (eligibilityDebounceTimer) clearTimeout(eligibilityDebounceTimer)

  // if not enough data, skip
  if (!form.member_id || !form.loan_product_id || !form.applied_amount) {
    eligibility.eligible = true
    eligibility.reason = ''
    return
  }

  // small debounce to avoid spamming server
  eligibilityDebounceTimer = setTimeout(async () => {
    try {
      await checkEligibilityServer()
    } catch (e) {
      // already handled in checkEligibilityServer (showMessage)
    }
  }, 500)
}

/* Trigger initial check when relevant props change (members/products) */
watch(() => form.member_id, () => { triggerEligibilityCheckDebounced() })
watch(() => form.loan_product_id, () => { triggerEligibilityCheckDebounced() })
watch(() => form.applied_amount, () => { triggerEligibilityCheckDebounced() })

/* --------------------------------------------------------------- */

/* submit controls - updated to include eligibility check */
const canSubmit = computed(() => {
  const hasBasicInfo =
    !!form.member_id &&
    !!form.loan_product_id &&
    !!form.applied_amount &&
    !!form.term_months &&
    !!form.purpose

  const hasDisbursement =
    !!disbursementMethod.value &&
    (
      (disbursementMethod.value === 'mpesa' && !!disbursementDetails.mpesaNumber) ||
      (disbursementMethod.value === 'bank' &&
        !!disbursementDetails.bankName &&
        !!disbursementDetails.branch &&
        !!disbursementDetails.accountNumber) ||
      (disbursementMethod.value === 'cheque' && !!disbursementDetails.payee)
    )

  const hasDocuments =
    (uploadedFiles.combined !== null) ||
    (Array.isArray(uploadedFiles.multiple) && uploadedFiles.multiple.length > 0)

  // Also require that eligibility has been checked and is true.
  // If eligibility.checking is true, we conservatively disallow submit (to avoid race).
  const eligibilityOk = eligibility.eligible === true && !eligibility.checking

  return hasBasicInfo && hasDisbursement && hasDocuments && eligibilityOk
})

/* feedback helper */
const showMessage = (type, message, errors = null, callback = null) => {
  window.scrollTo({ top: 0, behavior: 'smooth' })
  if (type === 'success') {
    successMessage.value = message
    errorMessages.value = null
  } else {
    errorMessages.value = errors || { general: [message] }
    successMessage.value = null
  }
  setTimeout(() => {
    successMessage.value = null
    errorMessages.value = null
    if (callback) callback()
  }, 8000)
}

/* reset form */
const resetForm = () => {
  form.member_id = isMemberRole.value ? (props.auth.user.member?.id || '') : ''
  form.loan_product_id = ''
  form.applied_amount = ''
  form.term_months = ''
  form.purpose = ''
  form.guarantors = []
  uploadedFiles.combined = null
  selectedMember.value = isMemberRole.value ? props.auth.user.member : null
  selectedProduct.value = null
  loanCalculation.value = null
  disbursementMethod.value = ''
  Object.assign(disbursementDetails, { mpesaNumber: '', bankName: '', branch: '', accountNumber: '', payee: '' })

  // reset eligibility
  eligibility.checking = false
  eligibility.eligible = true
  eligibility.reason = ''
}

/* submit flow (confirm -> send) */
const submitApplication = () => {
  // attempt to run a final eligibility check before showing confirm modal.
  // If eligibility fails, show error and don't open confirm modal.
  // If check throws network error, we block submission and show message.
  showConfirm.value = false // ensure closed while checking
  processing.value = true
  checkEligibilityServer()
    .then(result => {
      if (!result.eligible) {
        // show message and prevent opening confirm
        showMessage('error', null, { eligibility: [result.reason || 'Member not eligible for this loan'] })
      } else {
        // open confirmation modal when eligible
        showConfirm.value = true
      }
    })
    .catch(() => {
      // checkEligibilityServer already called showMessage; keep confirm closed
    })
    .finally(() => {
      processing.value = false
    })
}


const confirmSubmit = async () => {
  showConfirm.value = false
  processing.value = true

  form.guarantors = form.guarantors.filter(
    g => g.member_id && g.guaranteed_amount
  )

  // Final server-side eligibility check before creating the loan (defense in depth)
  try {
    const result = await checkEligibilityServer()
    if (!result.eligible) {
      showMessage('error', null, { eligibility: [result.reason || 'Member is not eligible'] })
      processing.value = false
      return
    }
  } catch (err) {
    // If server check failed (network), stop and show message (already handled in checkEligibilityServer)
    processing.value = false
    return
  }

  const formData = new FormData()
  Object.keys(form).forEach(key => {
    if (key !== 'guarantors') {
      formData.append(key, form[key] ?? '')
    }
  })

  form.guarantors.forEach((g, i) => {
    Object.keys(g).forEach(k => formData.append(`guarantors[${i}][${k}]`, g[k] ?? ''))
  })

  // disbursement details
  Object.keys(disbursementDetails).forEach(k => {
    formData.append(k, disbursementDetails[k] ?? '')
  })

  // support documents (multiple)
  if (uploadedFiles.combined) {
    formData.append('documents[combined]', uploadedFiles.combined)
  } else if (Array.isArray(uploadedFiles.multiple) && uploadedFiles.multiple.length) {
    uploadedFiles.multiple.forEach((fileObj, idx) => {
      // include type metadata if backend expects it
      formData.append(`documents[${idx}][type]`, fileObj.type)
      formData.append(`documents[${idx}][file]`, fileObj.file)
    })
  }

  try {
    const response = await axios.post(route('loans.store'), formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })

    if (response.data?.success) {
      resetForm()
      showMessage('success', response.data.message, null, () => {
        if (isMemberRole.value) {
          router.visit(route('my-loans'))
        } else {
          router.visit(route('loans.index'))
        }
      })
    } else {
      showMessage('error', response.data?.message || 'Unexpected response from server.')
    }
  } catch (error) {
    if (error.response?.status === 422) {
      const data = error.response.data
      if (data.errors) {
        showMessage('error', null, data.errors)
      } else if (data.message) {
        showMessage('error', data.message)
      }
    } else {
      console.error('Unexpected error:', error)
      showMessage('error', 'Something went wrong. Please try again.')
    }
  } finally {
    processing.value = false
  }
}



// eligibility state
const isEligible = ref(true)
const ineligibleReasons = ref([])

/**
 * Checks loan eligibility
 */
const checkEligibility = async () => {
  // Assign fallback values if not provided
  const memberId = form.member_id
  const productId = form.loan_product_id || 1   // fallback to first product ID or 1
  const amount = form.applied_amount || 1       // fallback to 0

  if (!memberId) {
    console.log('No member ID available for eligibility check')
    return
  }

  try {
    const payload = {
      member_id: memberId,
      loan_product_id: productId,
      requested_amount: amount,
    }

    const response = await axios.post(route('members.loans.check-eligibility'), payload)

    const data = response.data.data

    isEligible.value = data.eligible
    ineligibleReasons.value = data.reasons || data.messages || []
    console.log('Eligibility data:', data)
  } catch (error) {
    console.error('Eligibility check failed:', error)
    isEligible.value = false
    ineligibleReasons.value = ['Unable to verify eligibility. Please try again later.']
  }
}

// Run automatically for member role
onMounted(() => {
  if (isMemberRole.value && props.auth?.member?.id) {
    form.member_id = props.auth.member.id
    checkEligibility()
  }
})

// Run automatically for admin when selecting a member
watch(() => form.member_id, (newVal) => {
  if (!isMemberRole.value && newVal) {
    checkEligibility()
  }
})


watchEffect(() => {
  if (!isEligible.value && (errorMessages.value || successMessage.value)) {
    setTimeout(() => {
      errorMessages.value = null
      successMessage.value = null
    }, 200)
  }
})



const resetMemberSelection = () => {
  form.member_id = '';
  selectedMember.value = null;
  isEligible.value = true;
  resetForm();
};



</script>

<style scoped>
/* small extras to enhance the modern feel (feel free to move into your global styles) */
input[type="file"] {
  display: none;
}

.rounded-2xl {
  border-radius: 1rem;
}
</style>
