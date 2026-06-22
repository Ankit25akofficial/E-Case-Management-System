export enum UserRole {
  SUPER_ADMIN = 'SUPER_ADMIN',
  COURT_ADMIN = 'COURT_ADMIN',
  JUDGE = 'JUDGE',
  LAWYER = 'LAWYER',
  CLIENT = 'CLIENT',
  CLERK = 'CLERK',
  STAFF = 'STAFF'
}

export enum CaseStatus {
  PENDING = 'PENDING',
  ASSIGNED = 'ASSIGNED',
  IN_PROGRESS = 'IN_PROGRESS',
  CLOSED = 'CLOSED',
  ARCHIVED = 'ARCHIVED'
}

export enum CasePriority {
  LOW = 'LOW',
  MEDIUM = 'MEDIUM',
  HIGH = 'HIGH',
  URGENT = 'URGENT'
}

export enum HearingStatus {
  SCHEDULED = 'SCHEDULED',
  COMPLETED = 'COMPLETED',
  POSTPONED = 'POSTPONED',
  CANCELLED = 'CANCELLED'
}

export enum DocApprovalStatus {
  PENDING = 'PENDING',
  APPROVED = 'APPROVED',
  REJECTED = 'REJECTED'
}
