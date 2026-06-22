import { PrismaClient } from '@prisma/client';
import { UserRole } from '../src/types/enums';
import bcrypt from 'bcryptjs';

const prisma = new PrismaClient();

async function main() {
  console.log('Seeding database...');

  // Create Court
  const court = await prisma.court.upsert({
    where: { name: 'Delhi High Court' },
    update: {},
    create: {
      name: 'Delhi High Court',
      location: 'Shershah Road, New Delhi',
      roomNumber: 'Court Room 1',
    },
  });
  console.log(`Court created: ${court.name}`);

  // Hashed Passwords
  const adminHash = await bcrypt.hash('admin123', 10);
  const clientHash = await bcrypt.hash('client123', 10);
  const judgeHash = await bcrypt.hash('judge123', 10);
  const lawyerHash = await bcrypt.hash('lawyer123', 10);

  // Users to create
  const users = [
    {
      username: 'admin',
      email: 'admin@ecms.gov.in',
      passwordHash: adminHash,
      fullName: 'System Administrator',
      role: UserRole.SUPER_ADMIN,
      isEmailVerified: true,
    },
    {
      username: 'client',
      email: 'client@example.com',
      passwordHash: clientHash,
      fullName: 'Demo Client',
      role: UserRole.CLIENT,
      isEmailVerified: true,
    },
    {
      username: 'judge_demo',
      email: 'judge@ecms.gov.in',
      passwordHash: judgeHash,
      fullName: 'Honorable Judge Sharma',
      role: UserRole.JUDGE,
      isEmailVerified: true,
    },
    {
      username: 'lawyer_demo',
      email: 'lawyer@example.com',
      passwordHash: lawyerHash,
      fullName: 'Advocate Rajesh Kumar',
      role: UserRole.LAWYER,
      isEmailVerified: true,
    },
  ];

  for (const userData of users) {
    const user = await prisma.user.upsert({
      where: { username: userData.username },
      update: {},
      create: {
        username: userData.username,
        email: userData.email,
        passwordHash: userData.passwordHash,
        fullName: userData.fullName,
        role: userData.role,
        isEmailVerified: userData.isEmailVerified,
        profile: {
          create: {
            phoneNumber: '9876543210',
            address: 'New Delhi, India',
            barNumber: userData.role === UserRole.LAWYER ? 'D/1234/2020' : null,
          },
        },
      },
    });
    console.log(`User created: ${user.username} (${user.role})`);
  }

  console.log('Seeding completed successfully!');
}

main()
  .catch((e) => {
    console.error(e);
    process.exit(1);
  })
  .finally(async () => {
    await prisma.$disconnect();
  });
