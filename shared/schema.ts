import { sql } from "drizzle-orm";
import { pgTable, text, varchar, timestamp } from "drizzle-orm/pg-core";
import { createInsertSchema } from "drizzle-zod";
import { z } from "zod";

export const users = pgTable("users", {
  id: varchar("id").primaryKey().default(sql`gen_random_uuid()`),
  username: text("username").notNull().unique(),
  password: text("password").notNull(),
});

export const contactSubmissions = pgTable("contact_submissions", {
  id: varchar("id").primaryKey().default(sql`gen_random_uuid()`),
  name: text("name").notNull(),
  email: text("email").notNull(),
  phone: text("phone").notNull(),
  subject: text("subject").notNull(),
  message: text("message").notNull(),
  createdAt: timestamp("created_at").defaultNow().notNull(),
});

export const trialRequests = pgTable("trial_requests", {
  id: varchar("id").primaryKey().default(sql`gen_random_uuid()`),
  name: text("name").notNull(),
  email: text("email").notNull(),
  phone: text("phone").notNull(),
  company: text("company"),
  websiteType: text("website_type").notNull(),
  budget: text("budget"),
  description: text("description"),
  createdAt: timestamp("created_at").defaultNow().notNull(),
});

export const insertUserSchema = createInsertSchema(users).pick({
  username: true,
  password: true,
});

export const insertContactSubmissionSchema = createInsertSchema(contactSubmissions).omit({
  id: true,
  createdAt: true,
}).extend({
  name: z.string().min(2, "Tên phải có ít nhất 2 ký tự").max(100, "Tên không được quá 100 ký tự"),
  email: z.string().email("Email không hợp lệ").max(255, "Email không được quá 255 ký tự"),
  phone: z.string().regex(/^[0-9+\-\s().]{10,15}$/, "Số điện thoại không hợp lệ"),
  subject: z.string().min(5, "Chủ đề phải có ít nhất 5 ký tự").max(200, "Chủ đề không được quá 200 ký tự"),
  message: z.string().min(10, "Tin nhắn phải có ít nhất 10 ký tự").max(2000, "Tin nhắn không được quá 2000 ký tự"),
});

export const insertTrialRequestSchema = createInsertSchema(trialRequests).omit({
  id: true,
  createdAt: true,
}).extend({
  name: z.string().min(2, "Tên phải có ít nhất 2 ký tự").max(100, "Tên không được quá 100 ký tự"),
  email: z.string().email("Email không hợp lệ").max(255, "Email không được quá 255 ký tự"),
  phone: z.string().regex(/^[0-9+\-\s().]{10,15}$/, "Số điện thoại không hợp lệ"),
  company: z.string().max(200, "Tên công ty không được quá 200 ký tự").optional(),
  websiteType: z.string().min(1, "Vui lòng chọn loại website"),
  budget: z.string().optional(),
  description: z.string().max(2000, "Mô tả không được quá 2000 ký tự").optional(),
});

export type InsertUser = z.infer<typeof insertUserSchema>;
export type User = typeof users.$inferSelect;
export type InsertContactSubmission = z.infer<typeof insertContactSubmissionSchema>;
export type ContactSubmission = typeof contactSubmissions.$inferSelect;
export type InsertTrialRequest = z.infer<typeof insertTrialRequestSchema>;
export type TrialRequest = typeof trialRequests.$inferSelect;
