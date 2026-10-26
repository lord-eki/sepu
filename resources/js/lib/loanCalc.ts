
const round2 = (n) => Math.round((n + Number.EPSILON) * 100) / 100

export function installment(principal, annualRatePercent, months) {
  const P = Number(principal) || 0
  const n = Number(months) || 0
  if (!P || !n) return 0

  const r = (Number(annualRatePercent) || 0) / 100 / 12
  if (r === 0) return round2(P / n)

  const f = Math.pow(1 + r, n)
  return round2((P * r * f) / (f - 1))
}

export function loanPreview(principal, annualRatePercent, months, processingRatePercent = 0, insuranceRatePercent = 0) {
  const P = Number(principal) || 0
  const n = Number(months) || 0
  const r = (Number(annualRatePercent) || 0) / 100 / 12
  const monthly = installment(P, annualRatePercent, n)

  let balance = P
  let totalInterest = 0
  for (let i = 1; i <= n; i++) {
    const interest = round2(balance * r)
    const principalPart = i === n ? balance : Math.min(monthly - interest, balance)
    balance = round2(balance - principalPart)
    totalInterest += interest
  }

  const processingFee = round2((P * (Number(processingRatePercent) || 0)) / 100)
  const insuranceFee = round2((P * (Number(insuranceRatePercent) || 0)) / 100)

  return {
    monthly,
    totalInterest: round2(totalInterest),
    totalRepayable: round2(P + totalInterest),
    processingFee,
    insuranceFee,
    netDisbursement: round2(P - processingFee - insuranceFee),
  }
}