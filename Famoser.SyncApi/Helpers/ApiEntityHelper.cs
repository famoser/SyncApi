using System;
using Famoser.SyncApi.Api.Communication.Entities;
using Famoser.SyncApi.Api.Communication.Entities.Base;
using Famoser.SyncApi.Enums;
using Famoser.SyncApi.Models.Information;
using Newtonsoft.Json;

namespace Famoser.SyncApi.Helpers
{
    public class ApiEntityHelper
    {
        private static T CreateApiEntity<T>(CacheInformations info, string identifier, Func<object> getModelFunc)
            where T : BaseEntity, new()
        {
            if (info.PendingAction == PendingAction.None)
                return null;

            var modl = new T()
            {
                Id = info.Id,
                VersionId = info.VersionId,
                OnlineAction = Convert(info.PendingAction),
                Identifier = identifier
            };
            if (info.PendingAction == PendingAction.Create || info.PendingAction == PendingAction.Update)
                modl.Content = JsonConvert.SerializeObject(getModelFunc.Invoke());
            if (info.PendingAction == PendingAction.Create)
                modl.CreateDateTime = info.CreateDateTime;

            return modl;
        }

        public static CollectionEntity CreateCollectionEntity(CacheInformations info, string identifier, Func<object> getModelFunc)
        {
            if (info.PendingAction == PendingAction.None)
                return null;

            var mdl = CreateApiEntity<CollectionEntity>(info, identifier, getModelFunc);
            if (mdl != null)
            {
                mdl.DeviceId = info.DeviceId;
                mdl.UserId = info.UserId;
            }
            return mdl;
        }

        public static SyncEntity CreateSyncEntity(CacheInformations info, string identifier, Func<object> getModelFunc)
        {
            if (info.PendingAction == PendingAction.None)
                return null;

            var mdl = CreateApiEntity<SyncEntity>(info, identifier, getModelFunc);
            if (mdl != null)
            {
                mdl.DeviceId = info.DeviceId;
                mdl.UserId = info.UserId;
                mdl.CollectionId = info.CollectionId;
            }
            return mdl;
        }

        private static T CreateCacheInformation<T>(BaseEntity entity, T existing = null)
            where T : CacheInformations, new()
        {
            if (existing == null)
                existing = new T();
            existing.Id = entity.Id;
            existing.VersionId = entity.VersionId;
            existing.PendingAction = PendingAction.None;
            existing.CreateDateTime = entity.CreateDateTime;
            return existing;
        }

        public static T CreateCacheInformation<T>(CollectionEntity entity, T exisiting = null)
            where T : CacheInformations, new()
        {
            var mi = CreateCacheInformation<T>(entity as BaseEntity, exisiting);
            mi.UserId = entity.UserId;
            mi.DeviceId = entity.DeviceId;
            return mi;
        }

        public static HistoryInformations<T> CreateHistoryInformation<T>(CollectionEntity entity)
        {
            return CreateCacheInformation(entity, new HistoryInformations<T>());
        }

        public static T CreateCacheInformation<T>(SyncEntity entity, T existing = null)
            where T : CacheInformations, new()
        {
            var mi = CreateCacheInformation(entity as CollectionEntity, existing);
            mi.CollectionId = entity.CollectionId;
            return mi;
        }


        private static OnlineAction Convert(PendingAction action)
        {
            switch (action)
            {
                case PendingAction.Create: return OnlineAction.Create;
                case PendingAction.Read: return OnlineAction.Read;
                case PendingAction.Update: return OnlineAction.Update;
                case PendingAction.Delete: return OnlineAction.Delete;
                default:
                    return OnlineAction.None;
            }
        }
    }
}
